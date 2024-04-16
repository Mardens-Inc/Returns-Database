/**
 * A class representing a Filemaker record.
 */
class FilemakerRecord {
    /**
     * Constructor for creating an instance of the class.
     *
     * @constructor
     * @memberof FilemakerRecord
     * @returns {void}
     */
    constructor() {
        this.fields = {};
        this.portalData = [];
        this.recordId = "0";
        this.modId = "0";
    }

    /**
     * Sets the value of a field in an object.
     *
     * @param {string} name - The name of the field to be set.
     * @param {string|number} value - The value to assign to the field.
     */
    setField(name, value) {
        this.fields[name] = value;
    }

    /**
     * Retrieves the value of a field based on its name.
     *
     * @param {string} name - The name of the field.
     * @return {string|number} - The value of the field.
     */
    getField(name) {
        return this.fields[name];
    }


    /**
     * Creates a new FilemakerRecord object from a JSON representation.
     *
     * @param {Object} json - The JSON object representing the FilemakerRecord.
     * @returns {FilemakerRecord} - The FilemakerRecord object created from the JSON representation.
     */
    static fromJSON(json) {
        let record = new FilemakerRecord();
        if (json === [] || json.fieldData === undefined || json.portalData === undefined || json.recordId === undefined || json.modId === undefined) {
            return record;
        }
        record.fields = json.fieldData;
        record.portalData = json.portalData;
        record.recordId = json.recordId;
        record.modId = json.modId;
        return record;
    }
}

/**
 * Represents a Filemaker API client.
 *
 * @class
 */
class Filemaker {

    /**
     * Creates a new instance of the Filemaker class.
     * @param {string} url - The url of the Filemaker API server.
     * @param {string} username - The username to use to connect to the server.
     * @param {string} password - The password to use to connect to the server.
     * @param {string} database - The database to use.
     * @param {string} layout - The layout/table to use.
     * @constructor
     */
    constructor(url, username = "", password = "", database = "", layout = "") {
        this.url = url;
        this.username = username;
        this.password = password;
        this.database = database;
        this.layout = layout;
        process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

    }

    /**
     * Sets the URL for the current instance.
     *
     * @param {string} url - The URL to be set.
     * @return {Filemaker} - The current instance of the Filemaker class.
     */
    withUrl(url) {
        this.url = url;
        return this;
    }

    /**
     * Sets the username.
     *
     * @param {string} username - The username to set.
     *
     * @return {Filemaker} - The current instance of the Filemaker class.
     */
    withUsername(username) {
        this.username = username;
        return this;
    }

    /**
     * Sets the password for the user.
     *
     * @param {string} password - The password to be set.
     * @return {Filemaker}
     */
    withPassword(password) {
        this.password = password;
        return this;
    }

    /**
     * Sets the database for the current instance.
     *
     * @param {string} database - The database name to be set.
     * @return {Filemaker} - The current instance with the database set.
     */
    withDatabase(database) {
        this.database = database;
        return this;
    }

    /**
     * Sets the layout for the component.
     *
     * @param {string} layout - The layout to be set for the component.
     * @returns {Filemaker} - Returns the current instance of the component.
     */
    withLayout(layout) {
        this.layout = layout;
        return this;
    }


    /**
     * Fetches a list of databases that have active sessions.
     * @returns {Promise<JSON>} - A array of active users
     * @throws {Error} - If the request fails
     */
    async getActiveSessions() {
        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        try {
            const response = await fetch(`${this.url}/auth/active`, {headers});
            return await response.json();
        } catch (e) {
            console.error(e);
        }

        throw new Error("Failed to fetch active users");
    }

    /**
     * Searches the database for records that match the query.
     *
     * @param {string} query - The query to search for.
     * @returns {Promise<FilemakerRecord[]>} - A Promise that resolves with an array of records that match the query.
     * @throws {Error} - If the request fails or if the username, password, database, or layout are not set.
     */
    async search(query) {
        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));


        try {
            const response = await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/search?query=${encodeURIComponent(query)}`, {headers});
            let json = await response.json();
            let records = [];
            for (let record of json) {
                records.push(FilemakerRecord.fromJSON(record));
            }
            return records;
        } catch (e) {
            console.error(e);
            throw new Error("Failed to search for records");
        }
    }

    /**
     * Performs an advanced search in the Filemaker database.
     *
     * @async
     * @param {Object} fields - The search fields and their values.
     * @param {Array<string>} sort - The field to sort the results by.
     * @param {boolean} ascending - Whether to sort the results in ascending order.
     * @throws {Error} - If required fields are not set or if the search fails.
     * @returns {Promise<FilemakerRecord[]>} - A promise that resolves to an array of FilemakerRecord objects representing the search results.
     */
    async advancedSearch(fields, sort = [], ascending = false) {
        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        try {
            const myHeaders = new Headers();
            myHeaders.append("X-Authentication-Options", `{"username": "${this.username}",    "password": "${this.password}" }`);
            myHeaders.append("Accept", "application/json");
            myHeaders.append("Content-Type", "application/json");

            const raw = JSON.stringify({fields: fields, sort: sort, ascending: ascending});

            const requestOptions = {
                method: "POST",
                headers: myHeaders,
                body: raw,
                redirect: "follow",
            };

            const response = await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/search`, requestOptions);
            const json = await response.json();
            return json.map((record) => FilemakerRecord.fromJSON(record));

        } catch (e) {
            console.error(e);
            throw new Error("Failed to search for records");
        }
    }

    /**
     * Retrieve the list of databases from the server.
     *
     * @throws {Error} Required fields are not set. Please set the username and password before making a request.
     * @throws {Error} Failed to fetch databases.
     *
     * @return {Promise<string[]>} A promise that resolves to the response data as JSON object.
     */
    async getDatabases() {
        if (this.username === "" && this.password === "") {
            throw new Error("Required fields are not set. Please set the username and password before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));
        try {
            const response = await fetch(`${this.url}/databases`, {headers});
            return Array.from(await response.json());
        } catch (e) {
            console.error(e);
            throw new Error("Failed to fetch databases");
        }
    }

    /**
     * Retrieves the layouts from the specified database.
     * Throws an error if the required fields (username, password, and database) are not set.
     *
     * @throws {Error} - If required fields are not set or if fetching the layouts fails.
     * @returns {Promise<string[]>} - A promise that resolves to an array of layout objects.
     */
    async getLayouts() {
        if (this.username === "" && this.password === "" && this.database === "") {
            throw new Error("Required fields are not set. Please set the username, password and database before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));
        try {
            const response = await fetch(`${this.url}/databases/${this.database}/layouts`, {headers});
            return Array.from(await response.json());
        } catch (e) {
            console.error(e);
            throw new Error("Failed to fetch layouts");
        }
    }

    /**
     * Fetches records from Filemaker database.
     *
     * @async
     * @param {number} limit - The maximum number of records to fetch. Default value is 10.
     * @param {number} offset - The offset position to start fetching records. Default value is 0.
     * @throws {Error} - If required fields (username, password, database, layout) are not set.
     * @throws {Error} - If failed to fetch records.
     * @returns {Promise<Array<FilemakerRecord>>} - A promise that resolves to an array of FilemakerRecord objects representing the fetched records.
     */
    async getRecords(limit = 10, offset = 0) {
        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));

        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            const response = await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/records?limit=${limit}&offset=${offset}`, {headers});
            let json = await response.json();
            let records = [];
            for (let record of json) {
                records.push(FilemakerRecord.fromJSON(record));
            }
            return records;
        } catch (e) {
            console.error(e);
            throw new Error("Failed to fetch records");
        }
    }

    /**
     * Retrieves a record from FileMaker with the specified ID.
     *
     * @param {string} id - The ID of the record to retrieve.
     * @returns {Promise<FilemakerRecord>} - A promise that resolves to the retrieved FilemakerRecord.
     * @throws {Error} - If the required fields (username, password, database, layout) are not set, or if the fetch operation fails.
     */
    async getRecord(id) {

        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));

        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            const response = await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/records/${id}`, {headers});
            let json = await response.json();
            return FilemakerRecord.fromJSON(json);
        } catch (e) {
            console.error(e);
            throw new Error("Failed to fetch records");
        }
    }

    /**
     * Retrieves the count of records in the specified database and layout.
     *
     * @throws {Error} Required fields are not set. Please set the username, password, database, and layout before making a request.
     * @throws {Error} Failed to fetch records
     *
     * @return {Promise<number>} The count of records.
     */
    async getRecordCount() {

        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));

        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            const response = await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/records/count`, {headers});
            let json = await response.json();
            return json.count;
        } catch (e) {
            console.error(e);
            throw new Error("Failed to fetch records");
        } finally {
            // Ensure that the NODE_TLS_REJECT_UNAUTHORIZED status is always reset to '1', even if an error is thrown
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '1';
        }
    }

    /**
     * Retrieves rows from the specified database and layout.
     *
     * @async
     * @throws {Error} If the required fields (username, password, database, layout) are not set.
     * @throws {Error} If the request to fetch records fails.
     * @returns {Promise<string[]>} A promise that resolves to an array of rows (JSON objects).
     */
    async getRows() {
        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));

        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            const response = await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/fields`, {headers});
            let json = await response.json();
            return Array.from(json);
        } catch (e) {
            console.error(e);
            throw new Error("Failed to fetch records");
        }

    }

    /**
     * Updates a record in the Filemaker database.
     *
     * @param {number} id - The ID of the record to update.
     * @param {FilemakerRecord} record - The updated record data.
     * @param {boolean} [addIfMissing=false] - If true, adds the record if it doesn't already exist.
     * @throws {Error} - If required fields are not set or if the update fails.
     * @returns {FilemakerRecord} - The updated record as a FilemakerRecord object.
     */
    async updateRecord(id, record, addIfMissing = false) {
        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("Content-Type", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));

        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            const response = await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/records/${id}?force-add=${addIfMissing}`, {
                method: "POST",
                headers,
                body: JSON.stringify(record["fields"])
            });
            let json = await response.json();
            return FilemakerRecord.fromJSON(json);
        } catch (e) {
            console.error(e);
            throw new Error("Failed to update record");
        }
    }

    /**
     * Deletes a record from the database.
     *
     * @param {string} id - The ID of the record to be deleted.
     * @throws {Error} If the required fields (username, password, database, layout) are not set.
     * @throws {Error} If the request to delete the record fails.
     */
    async deleteRecord(id) {
        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));

        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/records/${id}`, {
                method: "DELETE",
                headers
            });
        } catch (e) {
            console.error(e);
            throw new Error("Failed to delete record");
        }
    }

    /**
     * Deletes all records from the specified layout in the FileMaker database.
     *
     * @returns {Promise<void>} - A promise that resolves with no value when the deletion is successful.
     *
     * @throws {Error} - If the required fields (username, password, database, and layout) are not set.
     * @throws {Error} - If the deletion of records fails.
     */
    async deleteAllRecords() {
        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));

        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/records`, {
                method: "DELETE",
                headers
            });
        } catch (e) {
            console.error(e);
            throw new Error("Failed to delete records");
        }
    }

    /**
     * Add a record to the Filemaker database.
     *
     * @param {FilemakerRecord} record - The record to be added, must be a valid JSON object.
     * @throws {Error} If the required fields (username, password, database, and layout) are not set.
     * @returns {Promise<FilemakerRecord>} A promise that resolves with the added record as a FilemakerRecord object.
     * @throws {Error} If the record failed to be added.
     */
    async addRecord(record) {
        if (this.username === "" && this.password === "" && this.database === "" && this.layout === "") {
            throw new Error("Required fields are not set. Please set the username, password, database, and layout before making a request.");
        }

        // Set the accept headers to only accept json responses.
        const headers = new Headers();
        headers.set("Accept", "application/json");
        headers.set("Content-Type", "application/json");
        headers.set("X-Authentication-Options", JSON.stringify({username: this.username, password: this.password}));

        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            const response = await fetch(`${this.url}/databases/${this.database}/layouts/${this.layout}/records`, {
                method: "POST",
                headers,
                body: JSON.stringify(record["fields"])
            });
            let json = await response.json();
            return FilemakerRecord.fromJSON(json);
        } catch (e) {
            console.error(e, record);

            throw new Error("Failed to add record");
        }
    }

    /**
     * Validates the given credentials by sending a request to the server. If the credentials are valid, the username, password, and database are set.
     *
     * @param {string} username - The username to validate.
     * @param {string} password - The password to validate.
     * @param {string} database - The database to validate.
     *
     * @throws {Error} - If username, password, or database are missing.
     *
     * @returns {Promise<{success: boolean, error}>} - True if the credentials are valid, false otherwise.
     */
    async validateCredentials(username, password, database) {
        if (username === "" || password === "" || database === "") throw new Error("Username, password, and database are required fields");
        const body = JSON.stringify({username: username, password: password, database: database});
        try {
            process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
            const response = await fetch(`${this.url}/credentials`, {method: "POST", body: body, headers: {"Content-Type": "application/json", "accept": "application/json"}});
            if (response.ok) {
                this.username = username;
                this.password = password;
                this.database = database;
                return {success: true, message: "Credentials are valid"};
            } else {
                return {success: false, message: "Invalid credentials"};
            }
        } catch (e) {
            console.error(e);
            return {message: e, success: false};
        }
    }

    /**
     * Builds a table with the given items.
     * @param {FilemakerRecord[]} items - An array of items to populate the table with.
     * @param {string[]} [ignoredFields=[]] - An optional array of fields to ignore when populating the table.
     * @returns {HTMLTableElement} - A table element populated with the items.
     */
    static buildTable(items, ignoredFields = []) {
        const table = document.createElement("table");
        const head = document.createElement("thead");
        const body = document.createElement("tbody");
        const rowNames = Object.keys(items[0].fields);
        // Add the header row
        const header = document.createElement("tr");
        for (const row of rowNames) {
            if (ignoredFields.includes(row)) continue;
            const th = document.createElement("th");
            th.textContent = row;
            header.appendChild(th);
        }
        head.appendChild(header);
        table.appendChild(head);
        // Add the body rows
        for (const record of items) {
            const tr = document.createElement("tr");
            for (const row of rowNames) {
                if (ignoredFields.includes(row)) continue;
                const td = document.createElement("td");
                td.textContent = record.fields[row];
                tr.appendChild(td);
            }
            body.appendChild(tr);
        }
        table.appendChild(body);
        return table;
    }

}

module.exports = {Filemaker, FilemakerRecord};