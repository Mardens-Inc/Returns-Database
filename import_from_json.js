const fs = require("node:fs");

(async () => {
    console.log("Reading file...");
    const data = Array.from(JSON.parse(fs.readFileSync("items.json")));
    console.log(`Found ${data.length} records...\nSending records...`);

    process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

    let i = 0;
    for (const item of data) {
        try {
            fetch("http://returns.local/api/", {method: "POST", body: JSON.stringify(item), headers: {"Content-Type": "application/json"}}).then(() => {
                i++;
                console.log(`Sent ${i}/${data.length} (${i / data.length * 100}%) records`);
            });
        } catch (e) {
            console.log(e);
        }
    }

    console.log("Done!");
})()
