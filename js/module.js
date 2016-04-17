
function showmoreoptions(selectobj){

    for(i = 0 ; i < selectobj.options.length ; i++){
        var divid = selectobj.options[i].value + 'params';
        divobj = document.getElementById(divid);
        if (divobj){
            divobj.style.visibility = 'hidden';
            divobj.style.display = 'none';
        }
    }

    if (selectobj.selectedIndex){
        var divid = selectobj.options[selectobj.selectedIndex].value + 'params';
        divobj = document.getElementById(divid);
        if (divobj){
            divobj.style.visibility = 'visible';
            divobj.style.display = 'block';
        }
    }
}

function open_panel(panelid) {
    $('.dashboardsettings-panel').attr('class', 'dashboardsettings-panel off');
    $('#dashboardsettings-panel-'+panelid).attr('class', 'dashboardsettings-panel on');
    $('.setting-tab').removeClass('here');
    $('#setting-tab-'+panelid).addClass('here');
    $('.setting-tab').removeClass('active');
    $('#setting-tab-'+panelid).addClass('active');
}

var autosubmit = 1;

function submitdashboardfilter(instance){
    if (autosubmit){
        document.forms[instance].submit();
    }
}