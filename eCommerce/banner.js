document.observe("e:load", function(){
uf = $("banner_upload_field");
if(uf)
    uf.clonePosition("banner_upload_slot");
f = $("banner_upload_file");
i = $("banner_upload_info");
if(f) {
ext = f.value.substr(f.value.lastIndexOf('.'));
if(ext.toLowerCase()=='swf')
    i.innerHTML = '<object type="application/x-shockwave-flash" data="'+f.value+'" width="300" height="120"><param name="movie" value="'+f.value+'" /></object>';
else
    i.innerHTML = '<img src="'+f.value+'" style="max-width:300px;max-height:120px">';
}
});
