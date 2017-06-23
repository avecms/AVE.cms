// солов’їна))) 01,2017 duncan

var logoutTitle = "Вихід з панелі керування";
var logoutConfirm = "Ви впевнені, що бажаєте вийти?";
var clearCacheTitle = "Очистка кеша";
var clearCacheConfirm = "Ви впевнені, що бажаєте очистити кеш?";
var clearCacheSessTitle = "Очистка кеша та сесій";
var clearCacheSessConfirm = "Ви впевнені, що бажаєте очистити кеш та сесії?";
var clearThumbTitle = "Видалення мініатюр";
var clearThumbConfirm = "Ви впевнені, що бажаєте видалити всі мініатюри зображень<br/>з директорії для збереження файлів (UPLOAD_DIR)?";
var clearRevTitle = "Видалення ревізій документів";
var clearRevConfirm = "Ви впевнені, що бажаєте видалити все ревізії документів?";
var clearCountTitle = "Обнулити щоденний лічильник документів";
var clearCountConfirm = "Ви впевнені, що бажаєте обнулити щоденний лічильник документів?";
var cacheShowTitle = "Показати розмір кеша";
var cacheShowConfirm = "Ви впевнені, що бажаєте подивитися розмір кеша?<br />це може зайняти якийсь час.";
var ajaxErrorStatus = "Немає з’єднання.<br />Перевірте своє підключення.";
var ajaxErrorStatus404 = "Сторінку, яку шукаєте, не знайдено. [404]";
var ajaxErrorStatus401 = "Запит не може бути виконано.<br />Помилка авторизації для виконання цього запиту. [401]";
var ajaxErrorStatus500 = "Сталася внутрішня помилка.<br />Спробуйте повторити свій запит пізніше. [500]";
var ajaxErrorStatusJSON = "Некоректна відповідь сервера<br />Дані не в форматі JSON.";
var ajaxErrorStatusTimeOut = "Час запиту вийшов.";
var ajaxErrorStatusAbort = "Ajax запит перервано.";
var ajaxErrorStatusMess = "Помилка:<br />";
var delCascadTitle = "видалити зображення";
var delCascadConfirm = "Ви впевнені, що бажаєте видалити?";
var saveMessageOk = "Дані збережено";

//===== Date & Time Pickers =====//
$.datepicker.regional['ua'] = {
	closeText: 'Закрити',
	prevText: '<Назад',
	nextText: 'Вперед>',
	currentText: 'Нині',
	monthNames: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень',
		'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'
	],
	monthNamesShort: ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер',
		'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'
	],
	dayNames: ['Неділя', 'понеділок', 'вівторок', 'середа', 'четвер', 'п’ятница', 'субота'],
	dayNamesShort: ['нед', 'пон', 'вів', 'сер', 'чет', 'пят', 'суб'],
	dayNamesMin: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
	weekHeader: 'Не',
	dateFormat: 'dd.mm.yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['ua']);

$.timepicker.regional['ua'] = {
	timeOnlyTitle: 'Оберіть час',
	timeText: 'Час',
	hourText: 'Години',
	minuteText: 'Хвилини',
	secondText: 'Секунди',
	millisecText: 'мілісекунди',
	currentText: 'Зараз',
	closeText: 'Закрити',
	ampm: false
};
$.timepicker.setDefaults($.timepicker.regional['ua']);