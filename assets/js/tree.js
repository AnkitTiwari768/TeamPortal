$.fn.extend({
    treed: function (o) {

       var openedClass = 'fa fa-minus-square';
       var closedClass = 'fa fa-plus-square';

      if (typeof o != 'undefined'){
        if (typeof o.openedClass != 'undefined'){
        openedClass = o.openedClass;
        }
        if (typeof o.closedClass != 'undefined'){
        closedClass = o.closedClass;
        }
      };

        //initialize each of the top levels
        var tree = $(this);
        tree.addClass("tree");
        tree.find('li').has("ul").each(function () {
            var branch = $(this); //li with children ul
            branch.prepend("<i class=' " + closedClass + "'></i>");
            branch.addClass('branch');
           // tree.find('li').not('li.branch').css( "background", "yellow" );
		    tree.find('li.branch').css( "color", "#4088cd" );

            branch.on('click', function (e) {
                if (this == e.target) {
                    var icon = $(this).children('i:first');
                    icon.toggleClass(openedClass + " " + closedClass);
                    $(this).children().children().toggle();
                    if (branch.find('li').last()) {
                        branch.find('li').last().addClass('last');
                    }
                }
            })

            branch.children().children().toggle();
        });

        //fire event from the dynamically added icon
        tree.find('.branch .indicator').each(function(){
            $(this).on('click', function () {
                $(this).closest('li').click();


            });
        });

        //fire event to open branch if the li contains an anchor instead of text
        tree.find('.branch>a').each(function () {
            $(this).on('click', function (e) {
                $(this).closest('li').click();
                e.preventDefault();
            });
        });
        //fire event to open branch if the li contains a button instead of text
        tree.find('.branch>button').each(function () {
            $(this).on('click', function (e) {
                $(this).closest('li').click();
                e.preventDefault();
            });
        });
    }
});

//Initialization of treeviews

$('#tree1').treed();


/* $('.branch').each(function() {
	var slength =$(this).find('li').length;
	console.log(slength);
	if(slength==0){
		$(this).hide(slength);
	}
});  */
