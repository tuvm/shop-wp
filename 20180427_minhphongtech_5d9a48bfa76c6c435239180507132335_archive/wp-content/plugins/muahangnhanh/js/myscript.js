jQuery(document).ready(function(){
    jQuery('[data-popup-open]').on("click", function(e){
        jQuery('.cotphai').find('input[name="total"]').val(addCommas(price)+' đ');
    });
    //jQuery('.cotphai').find('input[name="total"]').val(addCommas(price)+' đ');
    jQuery('.cotphai').find('input[name="qty"]').change(function(){
        number = jQuery(this).val()*price;
        jQuery('.cotphai').find('input[name="total"]').val(addCommas(number) + ' đ');
    });
    frm =  jQuery('.cotphai');
    jQuery('.cotphai').find('button[name="submit"]').on( "click", function(e) {
        if(checkdata(getdata(frm,'name')) && checkdata(getdata(frm,'sdt')) && checkdata(getdata(frm,'email')) && checkdata(getdata(frm,'address'))){
            jQuery('.cotphai').append('<div class="notice">Bạn chưa điền đầy đủ thông tin</div>');
            setTimeout(function(){
                jQuery('.notice').remove();
            },2000)
            return false;
        }
        datastr = "name="+ getdata(frm,'name')+"&sdt="+getdata(frm,'sdt')+"&email="+getdata(frm,'email')+"&qty="+getdata(frm,'qty')+"&address="+getdata(frm,'address')+"&total="+getdata(frm,'qty')*price+"&product="+jQuery(".cottrai .title-wrapper").text()+"&from="+from+"&to="+to;
        e.preventDefault();
        jQuery('.web79loading').html("<img src='"+ blog_url +"/wp-content/plugins/muahangnhanh/image/giphy.gif' style='width:40px'>");
        jQuery.ajax({
            type: 'POST',
            url:  blog_url+ '/wp-content/plugins/muahangnhanh/sendmail.php',
            data: datastr,
            success: function (data) {
                jQuery('.web79loading').remove();
                jQuery('.cotphai').append('<div class="notice">Đặt hàng thành công</div>');
                setTimeout(function(){
					jQuery('.notice').remove();
                    jQuery('.popup-close').click();
                }, 3000);
                console.log('Submission was successful.');
                console.log(data);
            },
            error: function (data) {
                console.log('An error occurred.');
                console.log(data);
            },
        });
    });
});
function checkdata(req){
    if(req === '' || ! req){ return true;}
    else return false;
}
jQuery(function() {
    //----- OPEN
    jQuery('[data-popup-open]').on('click', function(e)  {
        var targeted_popup_class = jQuery(this).attr('data-popup-open');
        jQuery('[data-popup="' + targeted_popup_class + '"]').fadeIn(350);
 
        e.preventDefault();
    });
 
    //----- CLOSE
    jQuery('[data-popup-close]').on('click', function(e)  {
        var targeted_popup_class = jQuery(this).attr('data-popup-close');
        jQuery('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);
        e.preventDefault();
    });
    jQuery('.popup-inner').clickOff(function() {
             jQuery('[data-popup="muahangnhanh"]').fadeOut(350);
    });
});
jQuery.fn.clickOff = function(callback, selfDestroy) {
    var clicked = false;
    var parent = this;
    var destroy = selfDestroy || true;
    
    parent.click(function() {
        clicked = true;
    });
    
    jQuery('.popup').click(function(event) { 
        if (!clicked) {
            callback(parent, event);
        }
        if (destroy) {
            //parent.clickOff = function() {};
            //parent.off("click");
            //jQuery(document).off("click");
            //parent.off("clickOff");
        };
        clicked = false;
    });
};

function getdata(frm,req){
    return frm.find('input[name="'+req+'"]').val();
}
function addCommas(nStr)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}
document.addEventListener("DOMContentLoaded", function() {
    var elements = document.getElementsByTagName("INPUT");
    for (var i = 0; i < elements.length; i++) {
        elements[i].oninvalid = function(e) {
            e.target.setCustomValidity("");
            if (!e.target.validity.valid) {
                e.target.setCustomValidity("Bạn cần điền thông tin vào ô này");
            }
        };
        elements[i].oninput = function(e) {
            e.target.setCustomValidity("");
        };
    }
});