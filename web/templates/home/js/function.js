function modal_sms(sms)
{
 	$('body').append('<div id="added2cart" class="reveal-modal small" data-reveal><center><h5>'+ sms +'</h5></center><a class="close-reveal-modal">&#215;</a></div>');
    //Open the reveal modal
    $('#added2cart').foundation('reveal', 'open');
}

