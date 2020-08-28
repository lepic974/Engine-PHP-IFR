$.fn.jqCalendar = function (){
	$(this).each(function(){
		$(this).css('width','80px');
		inputId=$(this).attr('id');
		if(inputId){
			$(this).after('<img src="pic/ico_calendar.png" id="jqJsCalendar__'+inputId+'" style="border: 0px solid black; cursor: pointer;" title="Selectionner la date" align="absmiddle">');
			Calendar.setup({
				inputField 		: inputId,
				ifFormat 		: "%d/%m/%Y",
				button 			: "jqJsCalendar__"+inputId,
				align 			: "Tl",
				singleClick 	: true,
				onUpdate : jqCalendarOnUpdate
			});
		}
	});
}
function jqCalendarOnUpdate(cal){
	$(cal.params.inputField).focus().blur();
}