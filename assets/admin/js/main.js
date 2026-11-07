"use strict";

window.addEventListener("DOMContentLoaded", (event) => {
    const el = document.getElementById('filter_close_btn');
    if (el) {
        el.addEventListener('click', function (){
            let dropdownMenu = document.querySelector(".dropdown-menu.show");
            if (dropdownMenu) {
                dropdownMenu.classList.remove("show");
            }
        });
    }
});

window.addEventListener("DOMContentLoaded", (event) => {
    const el = document.getElementById('clear_filter');
    if (el) {
        el.addEventListener('click', function (){
            const filterDateRange = document.getElementById("filter_form");
            filterDateRange.reset();
        });
    }
});

window.addEventListener("DOMContentLoaded", (event) => {
    const el = document.getElementById('flatpickr_filter_date_range');
    if (el) {
        el.addEventListener('click', function (){
            const filterDateRange = document.getElementById("filter_date_range");
            if (filterDateRange) {
                filterDateRange.value = "";
            }
        });
    }
});



/*=========Extra custom script start===============*/

/*datatable tooltip*/
document.addEventListener("DOMContentLoaded", function () {
    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    initializeTooltips();
    $(document).on('draw.dt', function () {
        initializeTooltips();
    });
});


/*script tooltip or small text*/
function updateTooltip(selector, tooltipContent, isTooltip = false) {

    $('[data-bs-toggle="tooltip"]').tooltip();

    let tooltipElement = $(selector);
    if (isTooltip) {
        tooltipElement.attr('data-bs-original-title', tooltipContent).removeClass('d-none').addClass('ms-2');

        let tooltip = bootstrap.Tooltip.getInstance(tooltipElement[0]);
        if (tooltip) {
            tooltip.dispose();
        }
        new bootstrap.Tooltip(tooltipElement[0]);
    } else {
        tooltipElement.html(tooltipContent).removeClass('d-none').addClass('text-muted');
    }
}

/*format number*/
function formatAmount(value = 0, currencyLimit = 2) {
    const isCrypto = currencyLimit === 8;
    if (!isCrypto) {
        let formattedValue = parseFloat(value).toFixed(currencyLimit);
        if (currencyLimit === 2 && formattedValue.endsWith('.00')) {
            formattedValue = formattedValue.split('.')[0];
        }
        return formattedValue;
    }
    // For crypto, always show full limit (e.g., 8 decimal places)
    return parseFloat(value).toFixed(currencyLimit);
}



/*Notiflix loading*/
function blockExists(selector) {
    return document.querySelector(selector) !== null;
}

function showBlockLoading(blockId) {
    const selector = `[data-block="${blockId}"]`;
    if (blockExists(selector)) {
        Notiflix.Block.standard(selector, 'Please wait...', {
            backgroundColor: 'var(--bs-light)',
        });
    }
}


function hideBlockLoading(blockId) {
    let selector = `[data-block="${blockId}"]`;
    if (blockExists(selector)) Notiflix.Block.remove(selector);
}

axios.interceptors.request.use(config=>{const blockId=config.meta?.blockId;if(blockId)showBlockLoading(blockId);return config},error=>Promise.reject(error));axios.interceptors.response.use(response=>{const blockId=response.config.meta?.blockId;if(blockId)hideBlockLoading(blockId);return response},error=>{const blockId=error.config?.meta?.blockId;if(blockId)hideBlockLoading(blockId);return Promise.reject(error)});



/*see-more toggle for table row*/
function toggleText(element) {
    let parent = element.closest('td');
    let shortText = parent.querySelector('.short-text');
    let fullText = parent.querySelector('.full-text');
    shortText.classList.toggle('d-none');
    fullText.classList.toggle('d-none');
    element.textContent = fullText.classList.contains('d-none') ? 'See More' : 'See Less';
}

/*copy toggle for table row*/
function copyText(el) {
    let text = el.closest('td').querySelector('.full-text').textContent;
    navigator.clipboard.writeText(text).then(() => {
        el.textContent = 'Copied!';
        Notiflix.Notify.success('Copied!');
        setTimeout(() => el.textContent = 'Copy', 2000);
    }).catch(() => console.log('Failed to copy'));
}

function copyTextIcon(el) {
    const $el = $(el);
    const $icon = $el.find('i');
    const text = $el.closest('td').find('.full-text').text();

    navigator.clipboard.writeText(text).then(() => {
        $icon.removeClass('bi-clipboard').addClass('bi-clipboard-check');
        setTimeout(() => $icon.removeClass('bi-clipboard-check').addClass('bi-clipboard'), 5000);
        Notiflix.Notify.success('Copied!');
    });
}



function currencyPosition(amount, currency = {}) {
    const basic = window.basicControl;
    amount = parseFloat(amount).toFixed(2);
    const symbol = currency.symbol || basic.currency_symbol;
    const code = currency.code || basic.base_currency;
    const isLeft = basic.is_currency_position === 'left';
    const hasSpace = basic.has_space_between_currency_and_amount;
    return isLeft
        ? (hasSpace ? `${symbol} ${amount}` : `${symbol}${amount}`)
        : (hasSpace ? `${amount} ${code}` : `${amount}${code}`);
}


function fetchWithBlock(route, options = {}) {
    const blockId = options.meta?.blockId;
    if (blockId) showBlockLoading(blockId);

    return fetch(route, options)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (blockId) hideBlockLoading(blockId);
            return data;
        })
        .catch(error => {
            if (blockId) hideBlockLoading(blockId);
            throw error;
        });
}



