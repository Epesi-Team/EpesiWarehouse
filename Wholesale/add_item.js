function wholesale_add_item(iid) {
//    var form = f.evalJSON();
    var ccc = $('wholesale_add_item_queue');
    if(!ccc) {
        ccc = document.createElement('div');
        document.body.appendChild(ccc);
        ccc.style.position = "fixed";
        ccc.style.bottom = "20px";
        ccc.style.right = "20px";
//        ccc.style.width = "150px";
        ccc.style.zIndex = "10000";
        ccc.style.border = "2px solid black";
        ccc.style.background = "white";
        ccc.id = 'wholesale_add_item_queue';
    }
    var frame = document.createElement('iframe');
    frame.src = 'modules/Premium/Warehouse/Wholesale/add_item.php?iid='+iid;
    frame.style.width = "250px";
    frame.style.height = "50px";
    frame.style.border = "2px solid gray";
    frame.id = 'wholesale_add_item_'+iid;
    ccc.appendChild(frame);
}
