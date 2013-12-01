var collapsCatList = jQuery.noConflict();

collapsCatList(document).ready(function(){
  //collapsCatList('.children').slideUp();
  collapsCatList('.cat-item a[class^="expand"]').parent().children('ul.children').slideUp();

  collapsCatList('.cat-item a[class^="expand"], .cat-item a[class^="collapse"]').click(function(){

    if (collapsCatList(this).parent().children('ul.children').css('display') == 'none'){
      var src = collapsCatList(this).children('img').attr("src").replace("expand.gif", "collapse.gif");
    }else{
      var src = collapsCatList(this).children('img').attr("src").replace("collapse.gif", "expand.gif");
    }
    collapsCatList(this).children('img').attr("src", src);
    collapsCatList(this).parent().children('ul.children').slideToggle();

    return false;
  });
});


