<style>
    * {
        font-family: Arial, Helvetica, sans-serif;
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
        padding: 0;
    }

    body {
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        word-wrap: break-word;
        page-break-inside: auto;
        font-size: 11px;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    th,
    td {
        vertical-align: middle;
    }

    td:not(.p-0),
    th:not(.p-0) {
        padding: 2px 4px !important;
        margin: 0 !important;
    }

    table td table tr td,
    table td table tr th {
        padding: 0 !important;
    }

    table.p tr td,
    table.p tr th {
        padding: 2px 4px !important;
    }

    table.p0 tr td,
    table.p0 tr th {
        padding: 0 !important;
    }

    p,
    ul,
    ol {
        margin-top: 0;
        margin-bottom: 6px;
        line-height: 1.25;
    }

    ul,
    ol {
        padding-left: 20px;
        page-break-inside: auto !important;
    }

    li {
        margin-bottom: 2px;
        line-height: 1.25;
        text-align: justify;
    }

    table th {
        background-color: #686666;
        color: white;
    }

    tr.bold td {
        font-weight: bold;
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
        page-break-after: always;
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
