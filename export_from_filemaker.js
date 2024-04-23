const {Filemaker} = require("./Filemaker.js");
const fs = require("node:fs");

const filemaker = new Filemaker("https://lib.mardens.com/fmutil", "admin", "19MRCC77!", "Mardens_returns", "Reports-ListView");
filemaker.getRecordCount()
    .then(async count => {
        console.log("Populating records...");
        const records = [];
        const limit = 1000;
        for (let i = 1; i <= count; i += limit) {
            try {
                const startTime = new Date().getTime();
                const data = await filemaker.getRecords(limit, i);
                data.forEach(record => {
                    if (record["fields"]["store"] === undefined || record["fields"]["store"] === "" || record["fields"]["store"] === null) {
                        console.error(data)
                        return;
                    }
                })
                records.push(...data);
                const endTime = new Date().getTime();
                const duration = endTime - startTime;
                const current_items_per_second = limit / (duration / 1000);
                console.log(`Fetched ${i}/${count} (${(records.length / count) * 100}%) records. ETA: ${calculateDuration(current_items_per_second, count - i)}`);
            } catch (e) {
                console.log(e);
            }

        }
        return records;
    })
    .then(records => {
        let completed = 0;
        const items = [];
        records.forEach(async record => {
            record = record["fields"];
            if (record["store"] === undefined || record["store"] === "" || record["store"] === null) {
                console.error(record)
                return;
            }

            function formatToDesired(date) {
                const year = date.getUTCFullYear();
                const month = `0${date.getUTCMonth() + 1}`.slice(-2);
                const day = `0${date.getUTCDate()}`.slice(-2);
                const hours = `0${date.getUTCHours()}`.slice(-2);
                const minutes = `0${date.getUTCMinutes()}`.slice(-2);
                const seconds = `0${date.getUTCSeconds()}`.slice(-2);
                const milliseconds = `00${date.getUTCMilliseconds()}`.slice(-6);
                return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}.${milliseconds}`;
            }

            const date = new Date(record["CreatedDate"]);
            const formattedDate = formatToDesired(date);

            function getStore(city) {
                const map = {
                    "madawaska": 1,
                    "presque isle": 2,
                    "houlton": 3,
                    "lincoln": 4,
                    "calais": 5,
                    "brewer": 6,
                    "waterville": 7,
                    "ellsworth": 8,
                    "lewiston": 9,
                    "gray": 10,
                    "scarborough": 11,
                    "biddeford": 12,
                    "sanford": 13,
                }
                return map[city.toLowerCase()];
            }


            const item = {
                "date": {
                    "date": formattedDate,
                    "timezone_type": 3,
                    "timezone": "UTC"
                },
                "type": 0,
                "card": {
                    "date": {
                        "date": formattedDate,
                        "timezone_type": 3,
                        "timezone": "UTC"
                    },
                    "amount": record["Amount"],
                    "card": record["CardNumber"]
                },
                "employee": record["IssuedBy"],
                "customer": {
                    "city": record["Customers::City"],
                    "address": record["Customers::Address"],
                    "first_name": record["Customers::FirstName"],
                    "last_name": record["Customers::LastName"],
                    "email": "",
                    "phone": record["Customers::phone"],
                    "zip": record["Customers::Zip"],
                    "state": record["Customers::State"],
                    "date_of_birth": "1900-01-01"
                },
                "store": getStore(record["store"]),
            }

            items.push(item);
        });

        fs.writeFileSync(__dirname + "/items.json", JSON.stringify(items));

    });


function calculateDuration(current_items_per_second, total_items) {
    const seconds = total_items / current_items_per_second;
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    let message = ``;
    if (days > 0) {
        message += `${days}d `;
    }
    if (hours > 0) {
        message += `${Math.floor(hours % 24)}h `;
    }
    if (minutes > 0) {
        message += `${Math.floor(minutes % 60)}m `;
    }
    message += `${Math.floor(seconds % 60)}s`;
    return message;
}