
(function($){

    var methods = {

        init : function(options){

            var settings    = {}
            var username;
            var password;
            var idform      = $(this).attr('id');
            
            $.extend(settings, options);

        },
        _vertical : function(params){
             var idform     = $(this).attr('id');
             var data       = $('#'+idform).serialize();
             var nameMetod  = '_'+params.metod+'Vertical';
             
             if(params.metod == 'viewUpdate' ){
                data = params.id;
             }
             methods[nameMetod](data,params.metod);
        },
        _registerVertical : function(data,metod){
            $.ajax({
                url: basepath+'/index.php/crud/vertical/'+metod+'/create/'+data,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    $.fancybox(json.message);
                    $('#verticalname').val('');
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _viewUpdateVertical : function(data,metod){
            $.ajax({
                url: basepath+'/index.php/crud/vertical/'+metod+'/update/'+data,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  $('.inner-wrap').html(json.content);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },_updateVertical : function(data){
            $.ajax({
                url: basepath+'/index.php/crud/vertical/update/update/'+data.data,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  if(json.status){
                    $('.inner-wrap').html(json.content);  
                  }
                  
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });

        },_removeVertical : function(data,metod){
            //alert(metod);
            /*
            $.ajax({
                url: basepath+'/index.php/crud/vertical/'+metod+'/create/'+data,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    $.fancybox(json.message);
                    $('#verticalname').val('');
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });*/
        },
        _registerDisplay : function(params){

            var data = $("#"+params.form).serialize();
            $.ajax({
                url: basepath+'/index.php/crud/display/'+params.metod+'/create/'+data,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    $.fancybox(json.message);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _viewUpdateDisplay : function(params){

            $.ajax({
                url: basepath+'/index.php/crud/display/'+params.metod+'/update/'+params.id,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  $('.inner-wrap').html(json.content);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },_updateDisplay : function(data){
            $.ajax({
                url: basepath+'/index.php/crud/display/update/update/'+data.data,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  if(json.status){
                    $('.inner-wrap').html(json.content);  
                  }
                  
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _viewUpdateBlockcontent : function(params){

            $.ajax({
                url: basepath+'/index.php/crud/blockcontent/'+params.metod+'/update/'+params.id,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  $('.inner-wrap').html(json.content);
                  var htmlContent = $("#textareacontent").val();
                  var cssContent = $("#textareastylesheet").val();
                  $(".inner-wrap").documentGenerator("preview",{html:htmlContent,css:cssContent});
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _updateBlockContent : function(data){
            $.ajax({
                url: basepath+'/index.php/crud/blockcontent/update/update/false',
                type: 'POST',
                async: true,
                data: {
                    info: data.data
                },
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  if(json.status){
                    $('.inner-wrap').html(json.content);
                  }
                  
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _prevBlockContent : function(data){

            $.ajax({
                url: basepath+'/index.php/crud/blockcontent/prev/block/'+data.id,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  //$(".inner-wrap").append(json.htmlContent);
                  $.fancybox(json.htmlContent);                
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });

        },
        preview : function(html,css){
          $.ajax({
                url: basepath+'/index.php/preview',
                type: 'POST',
                async: true,
                dataType: 'json',
                data: {
                  html:html,
                  css:css
                },
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  
                  $('.prev-content-box').html(json.htmlPreview);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _viewUpdateSettingContent : function(params){
            $.ajax({
                url: basepath+'/index.php/crud/settingcontent/'+params.metod+'/update/'+params.id,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  $('.inner-wrap').html(json.content);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _newProduct : function(params){
            $.ajax({
                url: basepath+'/index.php/createnewdocument/new',
                type: 'POST',
                async: true,
                data: {
                    params:params.dataForm
                },
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                  //$('.inner-wrap').html(json.content);
                  window.top.location= basepath+'/index.php/createnewdocument/'+json.id
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _generateDocument : function(params){
            $.ajax({
                url: basepath+'/index.php/document/generate/'+params.id,
                type: 'POST',
                async: true,
                data: {
                    params:params.dataForm
                },
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){

                    $('.reveal-modal').remove();
                    $(".inner-wrap").html(json.content);
                    $('body').append('<div id="added2cart" class="reveal-modal small" data-reveal><center><h5>'+ json.message +'</h5></center><a class="close-reveal-modal">&#215;</a></div>');
                    $('#added2cart').foundation('reveal', 'open');
                    $('.tooltip').remove();
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _verticalFile : function(params){
            
            $.ajax({
                url: basepath+'/index.php/document/display/'+params.id,
                type: 'POST',
                async: true,
                data: {
                    parent:params.parentId
                },
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    $(".inner-wrap").html(json.listDisplay);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _createDisplay : function(params){

            $.ajax({
                url: basepath+'/index.php/document/paramsdisplay/'+params.id,
                type: 'POST',
                async: true,
                data: {
                    parent:params.parentId
                },
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    $(".inner-wrap").html(json.setting);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
            
        },
        _createDocument : function(params){

            var dataDisplay = $("#form-setting-display").serializeArray();

            $.ajax({
                url: basepath+'/index.php/document/createdocument/'+params.id,
                type: 'POST',
                async: true,
                data: {
                    params:dataDisplay,
                    file:params.file
                },
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    var status = Boolean(json.status);

                    $('.block-input label input').removeClass('error-input');
                    $('.reveal-modal').remove();

                    if(!status){
                        $('body').append('<div id="added2cart" class="reveal-modal small" data-reveal><center><h5>'+ json.message +'</h5></center><a class="close-reveal-modal">&#215;</a></div>');
                        $('#added2cart').foundation('reveal', 'open');

                        $.each(json.emptyValues, function( index, value ) {
                            $('#'+value).addClass('error-input');
                        });
                    }else{
                        top.location.href = 'http://'+json.uri;                        
                    }

                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _editDocument : function(params){

            $.ajax({
                url: basepath+'/index.php/document/editdocument/'+params.id,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    //var status = Boolean(json.status);
                     $('.inner-wrap').html(json.setting);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
            
        },
        _updateDocument : function(params){

            var dataDisplay = $("#form-setting-display-update").serializeArray();

            $.ajax({
                url: basepath+'/index.php/document/updatedocument/'+params.id,
                type: 'POST',
                async: true,
                data: {
                    params:dataDisplay
                },
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    var status = Boolean(json.status);

                    $('.block-input label input').removeClass('error-input');

                    if(!status){
                        $('body').append('<div id="added2cart" class="reveal-modal small" data-reveal><center><h5>'+ json.message +'</h5></center><a class="close-reveal-modal">&#215;</a></div>');
                        $('#added2cart').foundation('reveal', 'open');

                        $.each(json.emptyValues, function( index, value ) {
                            $('#'+value).addClass('error-input');
                        });
                    }else{
                        top.location.href = 'http://'+json.uri;                        
                    }

                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _getDocument : function(data){
            $.ajax({
                url: basepath+'/index.php/document/getdocument',
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    $('.inner-wrap').html(json.content);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        }
        ,
        _viewCreateDocument : function(params){
            $.ajax({
                url: basepath+'/index.php/document/create/'+params.id,
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    $('.inner-wrap').html(json.content);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        },
        _viewVerticalParent : function(){
            $.ajax({
                url: basepath+'/index.php/document/verticalparent',
                type: 'POST',
                async: true,
                dataType: 'json',
                beforeSend: function(xhr){
                    $.fancybox.showLoading();
                },
                success: function(json){
                    $('.inner-wrap').html(json.content);
                },
                complete: function(xhr, textStatus){
                    $.fancybox.hideLoading();
                }
            });
        }


    };

    $.fn.documentGenerator = function( method ) {  
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
        } else if ( typeof method === 'object' || ! method ){
            return methods.init.apply( this, arguments );
        } else {  
            $.error( 'Este método ' +  method + ' no existe en jQuery.'+method+'' );  
        }
    };

})( jQuery );