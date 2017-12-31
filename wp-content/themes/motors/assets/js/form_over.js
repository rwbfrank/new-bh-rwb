jQuery(document).ready(function($){
  $('#quick-quote form:first').submit(function(){
    var foo = {};
    $(this).find('input[type=text], select').each(function(){
      foo[$(this).attr('name')] = $(this).val();
    });
    document.cookie = 'formData='+JSON.stringify(foo);
  });
  var ff = $('#container form:first');
  if(ff.length){
    var data = $.parseJSON(
      document.cookie.match('(^|;) ?formData=([^;]*)(;|$)')[2]
    );
    if(data){
      for(var name in data){
        ff.find('input[name='+name+'], select[name='+name+']').val(data[name]);
      }
    }
  }
});