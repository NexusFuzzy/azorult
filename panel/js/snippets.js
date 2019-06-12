

function deleteRowsss(s, r)
{
var i = r.parentNode.parentNode.rowIndex;
document.getElementById(s).deleteRow(i);
};

function deleteRow(r) {
    var i = r.parentNode.parentNode.rowIndex;
    document.getElementById("reportsgrid").deleteRow(i);
}


