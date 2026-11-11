
$('#messages-icon').on('click',function (e){      
$("#msg-red-sutff").css("display","none"); 
   $.ajax({
   type:'POST',
   url :"MarkMessages.php",
   dataType: 'json',
   success: function(data) {
     //   console.log('success',data);
	 

   },
 //  error:function(exception){alert('Exeption:'+exception);}
}); 
 e.preventDefault();
});




$('#logs-icon').on('click',function (e){      
$("#logs-red-sutff").css("display","none"); 
   $.ajax({
   type:'POST',
   url :"MarkLogs.php",
   dataType: 'json',
   success: function(data) {
     //   console.log('success',data);
	 

   },
 //  error:function(exception){alert('Exeption:'+exception);}
}); 
 e.preventDefault();
});


