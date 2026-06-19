/**
* 2007-2023 Helloshop
*
* Tracking Center
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelector('body').addEventListener('click', function(event) {
		
        console.log(event.target.tagName.toLowerCase(), event.target.className);
        if (event.target.tagName.toLowerCase() === 'button' && event.target.className.indexOf('show') > -1) {
            event.target.classList.toggle("active");
            var panel = event.target.closest(".tracking-box").querySelector('.more-content');
			panel.classList.toggle("hlactive");
            if (panel.style.maxHeight) {
				panel.style.maxHeight = null;
			} else {
                panel.style.maxHeight = panel.scrollHeight + "px";
			}
			setTimeout(function(){ onStrat(); }, 100);
        }
		
		
    });
    // document.querySelectorAll('button.show').forEach(function(button) {
    //     button.addEventListener('click', function(e) {
    //         this.classList.toggle("active");
    //         var panel = this.closest(".tracking-box").querySelector('.more-content');
    //         if (panel.style.maxHeight) {
    //             panel.style.maxHeight = null;
    //         } else {
    //             panel.style.maxHeight = panel.scrollHeight + "px";
    //         }
    //     });
    // });
})


curbackground = [];
$( document ).ready(function() {
	$(".step-list").each(function() {
		hlid = $(this).data("id");
			
      curbackground[hlid] = $( ".step-list."+hlid+" .wrapper.current_status.step-3 .wrapper" ).css( "background" );
	  $( ".step-list."+hlid+" .wrapper.current_status.step-3 .wrapper" ).css( "background", "#c8c8c8" );
	  if ($(".step-list."+hlid+" .wrapper").hasClass("step-4")) {
		 $(".step-list."+hlid+" .item-rows .icon.i-step-3").addClass("do-step-4");
	   }
	   if(!$(".step-list."+hlid+" .wrapper").hasClass("current_status")){
		   runAnim(hlid);
	   }
	});
    $(".more-content:last").toggleClass("hlactive");
	
});

$(window).on("load  scroll",function(e) {
	setTimeout(function(){ onStrat(); }, 100);
});


function onStrat() {
	
	$(".step-list").each(function() {
		
		if (isElementInViewport($(this)) && !$(this).hasClass("viewed")) {
			
			hlid = $(this).data("id");
			
			//var doAnim = $(".step-list."+hlid+" .wrapper").hasClass("current_status");
			
			//if (doAnim) {
				setTimeout(function(){ runAnim(hlid); }, 900);
			//}
		}
	});
	
}


function runAnim(id) 
{
	$(".step-list."+id+" .icon.i-step-1").addClass("viewed");
	$(".step-list."+id+" .wrapper.step-2 span.wrapper").addClass("viewed");
	$(".step-list."+id+" .wrapper.step-3 .border").addClass("viewed");
	setTimeout(function(){ $( ".step-list."+id+" .wrapper.current_status.step-3 .wrapper" ).css( "background", curbackground[id] ); }, 1000);
}

function isElementInViewport (el) {
	
        if (!el || el.length == 0) {
            return false;
        }
		
		if (!isAccordianOpen(el)) {
            return false;
        }
		
        if (typeof jQuery === "function" && el instanceof jQuery) {
            el = el[0];
        }
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
            rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
        );
}

function isAccordianOpen (el) {
       
		if (el.parent().closest('.more-content').hasClass("hlactive")) {
			return true;
		}
		return false;
}