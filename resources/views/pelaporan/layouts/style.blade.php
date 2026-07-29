<style>
    * {
        font-family: Arial, Helvetica, sans-serif;
        box-sizing: border-box;
    }

    html {
        margin: 0;
    }

    table {
        word-wrap: break-word;
        border-collapse: collapse;
        page-break-inside: avoid;
        width: 100%;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    table tr th,
    table tr td,
    table tr td table.p tr td {
        padding: 2px 4px !important;
        margin: 0 !important;
        border-collapse: collapse;
        vertical-align: middle;
        font-size: 11px;
    }

    table tr td table tr td {
        padding: 0 !important;
    }

    table.p0 tr th,
    table.p0 tr td {
        padding: 0px !important;
    }

    .page-break {
        page-break-before: always;
    }

    .break {
        page-break-after: always;
    }

    .l {
        border-left: 1px solid #000;
    }

    .t {
        border-top: 1px solid #000;
    }

    .r {
        border-right: 1px solid #000;
    }

    .b {
        border-bottom: 1px solid #000;
    }
</style>
