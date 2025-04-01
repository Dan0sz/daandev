document.addEventListener('DOMContentLoaded', function () {
    /**
     * Makes sure the list item's Radio Button is selected upon every click.
     */
    document.querySelectorAll('.edd_price_options ul li').forEach(function (item) {
        item.addEventListener('click', function () {
            item.querySelector('input[type="radio"]').checked = true;
        });
    });
});
