var logoutTitle = "Изход от панела за управление";
var logoutConfirm = "Сигурни  ли сте, че желаете да излезете?";
var clearCacheTitle = "Изтриване на кеша";
var clearCacheConfirm = "Сигурни ли сте, че желаете да изтриете кеша?";
var clearCacheSessTitle = "Изтриване на кеша и сесиите";
var clearCacheSessConfirm = "Сигурни ли сте, че желаете да изтриете кеша и сесиите?";
var clearThumbTitle = "Изтриване на миниатюрите(thumbails)";
var clearThumbConfirm = "Сигурни ли сте, че желаете да изтриите всички миниатюри на изображенията<br/>от директорията за съхраняване на файлове (UPLOAD_DIR)?";
var clearRevTitle = "Изтриване ревизиите на документите";
var clearRevConfirm = "Сигурни ли сте, че желаете да изтриете всички ревизии на документите?";
var clearCountTitle = "Зануляване на дневния брояч на документите";
var clearCountConfirm = "Сигурни ли сте, че желаете да изтриите дневния брояч на документите?";
var cacheShowTitle = "Покажи размера на кеша";
var cacheShowConfirm = "Сигурни ли сте, че желаете да видите размера на кеша?<br />Това може да отнеме известно време.";
var ajaxErrorStatus = "Няма съединение.<br />Моля, провете данните за връзка.";
var ajaxErrorStatus404 = "Търсената страница не е намерена. [404]";
var ajaxErrorStatus401 = "Заявката не може да бъде изпълнена.<br />Грешка при авторизация за изпълнение на тази заявка. [401]";
var ajaxErrorStatus500 = "Вътрешна грешка.<br />Пробвайте да повторите своята заявка по-късно. [500]";
var ajaxErrorStatusJSON = "Некоректен отговор на сървъра<br />Данните не са в JSON формат.";
var ajaxErrorStatusTimeOut = "Времето за заявка изтече.";
var ajaxErrorStatusAbort = "Ajax заявката е прекъсната.";
var ajaxErrorStatusMess = "Грешка:<br />";
var delCascadTitle = "Изтриване на изображение";
var delCascadConfirm = "Сигурни ли сте, че желаете да изтриете?";
var saveMessageOk = "Данните са записани";

//===== Date & Time Pickers =====//
$.datepicker.regional['bg'] = {
	closeText: 'Затвори',
	prevText: '<Пред',
	nextText: 'След>',
	currentText: 'Днес',
	monthNames: ['Януари', 'Февруари', 'Март', 'Април', 'Май', 'Юни',
		'Юли', 'Август', 'Септември', 'Октомври', 'Ноември', 'Декември'
	],
	monthNamesShort: ['Яну', 'Фев', 'Мар', 'Апр', 'Май', 'Юни',
		'Юли', 'Авг', 'Сеп', 'Окт', 'Ное', 'Дек'
	],
	dayNames: ['неделя', 'понеделник', 'вторник', 'сряда', 'четвъртък', 'петък', 'събота'],
	dayNamesShort: ['нед', 'пон', 'вто', 'сря', 'чет', 'пет', 'съб'],
	dayNamesMin: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
	weekHeader: 'Не',
	dateFormat: 'dd.mm.yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['bg']);

$.timepicker.regional['bg'] = {
	timeOnlyTitle: 'Изберете време',
	timeText: 'Време',
	hourText: 'Час',
	minuteText: 'Минути',
	secondText: 'Секунди',
	millisecText: 'милисекунди',
	currentText: 'Сега',
	closeText: 'Затвори',
	ampm: false
};
$.timepicker.setDefaults($.timepicker.regional['bg']);