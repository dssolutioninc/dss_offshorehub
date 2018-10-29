jQuery(document).ready(function(){
"use strict";

/*** RESPONSIVE VERSION HEADER ***/		
var dropdowns = $('#main-menu > li > ul');
$('#main-menu > li > a').click(function() {
	$(dropdowns).slideUp();
	$(this).parent().find(dropdowns).slideToggle();
});
var dropdowns2 = $('#main-menu > li > ul > li ul');
$('#main-menu li > ul > li > a').click(function() {
	$(dropdowns2).slideUp();
	$(this).parent().find(dropdowns2).slideToggle();
});


$('.main-nav > span').click(function() {
	$(this).next('#main-menu').slideToggle();
});

  
    var count = 0;
    $('#click_down').click(function(){
        count =  parseInt($('input#adults').val());
        $('input#adults').val( count - 1);
       $('.adults-number').html( count - 1);
       update_tour_price();
    });
    $('#click_up').click(function(){
        count =  parseInt($('input#adults').val());
        $('input#adults').val( count + 1);
        $('.adults-number').html( count + 1);
        update_tour_price();
        
     });

update_tour_price();  
$('input#adults').on('change', function(){
    $('.adults-number').html( $(this).val() );
    $('#quantity').val( $(this).val() );
    update_tour_price();
});

$('input#childrenTiny').on('change', function(){
    $('.childrenTiny').html( $(this).val() );
    $('.childrenTiny').val( $(this).val() );
    update_tour_price();
});

$('input#children').on('change', function(){
    $('.children').html( $(this).val() );
    $('.children').val( $(this).val() );
    update_tour_price();
});

$('input#childrenHuge').on('change', function(){
    $('.childrenHuge').html( $(this).val() );
    $('.childrenHuge').val( $(this).val() );
    update_tour_price();
});

$('.book-now').on('click', function(){
    var dateWhen = $('#dateWhen').val();
       
    if( dateWhen !== '' ){
     $('#dateStart-booking').val($('#dateWhen').val());
     $("#tourModal").modal();
     
     $('#dateWhen').removeClass('errorBorder');
    } else{
        $('#dateWhen').addClass('errorBorder');
        $( '#dateWhen' ).focus();
    }
});

$('select#custom-field-1').change(function() {
    setTimeout(function(){ 
        update_tour_price();
        }, 500);
});

function update_tour_price() {
	var adults = $('input#adults').val();
        var children = $('input#children').val();
        var childrenHuge = $('input#childrenHuge').val();
        var transportVal = $('#custom-field-1').attr("data-snipcart-add-cost");
        var transportName = $('#custom-field-1').val();
        var priceCal = $('input#priceCal').val();
        $("input#transport").val(transportName);
        $("input#transportVal").val(transportVal);
       console.log(transportVal);
       if(transportVal === undefined){
           var price = Number(adults*priceCal + children *(priceCal*0.5) + childrenHuge*(priceCal*0.75)).toFixed(2);
       } else{
           var price = Number(adults*priceCal + children *(priceCal*0.5) + childrenHuge*(priceCal*0.75) + parseInt(transportVal)).toFixed(2);
       }
	
	var total_price = $('.total-cost').text().replace(/[\d\.\,]+/g, price);
	$('.total-cost').text( total_price );
        $('#total_booking').val("");
        $('#total_booking').val(price);
        
        
     
       
}
});