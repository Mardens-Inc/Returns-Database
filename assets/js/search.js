import {alert, openPopup} from 'https://cdn.jsdelivr.net/gh/Drew-Chase/ChaseUI@bf1436d5538cf5482e8c5d3345dd188e161d58db/js/popup.js';
import {startLoading, stopLoading} from 'https://cdn.jsdelivr.net/gh/Drew-Chase/ChaseUI@2cc1001a1764c8adc40bbaeef329506d34554b98/js/loading.js';

/**
 * @typedef Employee
 * @property {number} employee_id
 * @property {string} first_name
 * @property {string} last_name
 * @property {string} location
 */

let error_shown = false;
const main = $("main");
const nav = $("nav");
const table = $("table");
const input = $("nav input");
let debounce = 0;
let limit = 10;
let currentQuery = "";
try {
    limit = parseInt(window.location.search.split('&').filter(i => i.startsWith('limit'))[0].split('=').pop());
} catch {
    limit = 10;
}

if (isNaN(limit)) limit = 10

const limitSelect = $("select#limit");

let offset = 0;

limitSelect.on('change', async e => {
    const value = parseInt(e.target.value);
    if (isNaN(value)) return;
    limit = value;
    await search(input.val());
})

$(input).on('keyup', async () => {
    await search(input.val())
});
$(input).on('focusout', async () => {
    await search(input.val())
});

$(window).on('load', async () => {
    if (limitSelect.find("option[value='" + limit + "']").length > 0) {
        limitSelect.val(limit);
    } else {
        limit = limitSelect.val(10).val();
    }
    if (window.location.pathname.startsWith("/search")) {
        const params = new URLSearchParams(window.location.search);
        if (params.get("q") == null) window.history.replaceState({}, document.title, "/");
        $("nav input").val(params.get("q"));
        await search(params.get("q"));
    } else await search("")
})

async function search(query) {
    if (query === currentQuery) return;
    currentQuery = query;
    const body = table.find('tbody');
    if (query === "") {
        nav.removeClass("active")
        main.removeClass("active")
        window.history.replaceState({}, document.title, "/");
        body.empty();
        return;
    }
    if ($(".loading#search").length === 0) {
        body.html(startLoading({fullscreen: false, id: "search"}).css({width: "100%", margin: "auto", position: "absolute"})).css({position: "relative", "height": "300px"});
    }
    nav.addClass("active")
    main.addClass("active")
    window.history.replaceState({}, document.title, `/search?q=${query}&limit=${limit}&offset=${offset}`);
    clearTimeout(debounce);
    debounce = setTimeout(async () => {
        try {
            const response = (await $.get(`${window.location.hostname === "localhost" ? "http://returns.local" : ""}/api/search?q=${encodeURIComponent(query)}&limit=${limit}&offset=${offset}`));
            console.log(response)
            body.empty().css({position: "", "height": ""});
            Array.from(response).forEach((item) => {
                const row = $(`
                    <tr id="${item.id}" style="position:relative;">
                        <td>${item.customer.first_name}</td>
                        <td>${item.customer.last_name}</td>
                        <td>${item.store.city}</td>
                        <td>${item.date}</td>
                    </tr>
                `)
                row.append($(`<td style="position:absolute; right: 0; top: 50%; transform: translateY(-50%); background-color: transparent"><button class="view-button" title="View more information about this return."><i class="fa fa-eye"></i><span>View</span></button></td>`).on('click', async e => {
                    const id = $(e.target).closest('tr').attr('id');
                    await openPopup(`/assets/popups/view-return`, {id: item.id});
                }));
                body.append(row);
            });


        } catch (err) {
            console.error(err);
            if (!error_shown) {
                error_shown = true;
                alert("An error occurred while searching. Please try again later.", () => {
                    error_shown = false;
                });
            }
        }
        stopLoading("search");
    }, 500);
}

$("#discounts").on("click", () => {
    openPopup("/assets/popups/discounts");
});