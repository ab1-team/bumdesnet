<style>
    * {
        font-family: 'Arial', sans-serif;
        box-sizing: border-box;
    }

    table {
        word-wrap: break-word;
        border-collapse: collapse;
        page-break-inside: avoid;
        width: 100%;
        font-size: 11px;
    }

    table th {
        background-color: #686666;
        color: white;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    tr.bold td {
        font-weight: bold;
    }

    td:not(.p-0),
    th:not(.p-0) {
        padding: 2px 4px !important;
        margin: 0 !important;
        border-collapse: collapse;
        vertical-align: middle;
    }

    .row-white {
        background-color: #ffffff;
        color: #000;
    }

    .row-black {
        background-color: #e0e0e0;
        color: #000;
    }

    .page-break {
        page-break-before: always;
    }

    .break {
        page-break-after: always
    }

    .t {
        border-top: 1px solid black;
    }

    .l {
        border-left: 1px solid black;
    }

    .r {
        border-right: 1px solid black;
    }

    .b {
        border-bottom: 1px solid black;
    }
</style>
