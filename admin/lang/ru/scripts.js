var logoutTitle = "Выход из панели управления";
var logoutConfirm = "Вы уверены, что хотите выйти?";
var clearCacheTitle = "Очистка кэша";
var clearCacheConfirm = "Вы уверены, что хотите очистить кэш?";
var clearCacheSessTitle = "Очистка кэша и сессий";
var clearCacheSessConfirm = "Вы уверены, что хотите очистить кэш и сессии?";
var clearThumbTitle = "Удаление миниатюр";
var clearThumbConfirm = "Вы уверены, что хотите удалить все миниатюры изображений<br/>из директории для хранения файлов (UPLOAD_DIR)?";
var clearRevTitle = "Удаление ревизий документов";
var clearRevConfirm = "Вы уверены, что хотите удалить все ревизии документов?";
var clearCountTitle = "Обнулить подневный счетчик документов";
var clearCountConfirm = "Вы уверены, что хотите обнулить подневный счетчик документов?";
var cacheShowTitle = "Показать размер кеша";
var cacheShowConfirm = "Вы уверены, что хотите посмотреть размер кэша?<br />Это может занять какое-то время.";
var ajaxErrorStatus = "Нет соеденения.<br />Проверьте свое подключение.";
var ajaxErrorStatus404 = "Запрашиваемая страница не найдена. [404]";
var ajaxErrorStatus401 = "Запрос не может быть выполнен.<br />Ошибка авторизации для выполнения этого запроса. [401]";
var ajaxErrorStatus500 = "Произошла внутреняя ошибка.<br />Попробуйте повторить свой запрос позже. [500]";
var ajaxErrorStatusJSON = "Некорректный ответ сервера<br />Данные не в формате JSON.";
var ajaxErrorStatusTimeOut = "Вермя запроса вышло.";
var ajaxErrorStatusAbort = "Ajax запрос прерван.";
var ajaxErrorStatusMess = "Ошибка:<br />";
var delCascadTitle = "Удалить изображение";
var delCascadConfirm = "Вы уверены что хотите удалить?";
var saveMessageOk = "Данные сохранены";

//===== Date & Time Pickers =====//
$.datepicker.regional['ru'] = {
	closeText: 'Закрыть',
	prevText: '<Пред',
	nextText: 'След>',
	currentText: 'Сегодня',
	monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
		'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
	],
	monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
		'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'
	],
	dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
	dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
	dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
	weekHeader: 'Не',
	dateFormat: 'dd.mm.yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['ru']);

$.timepicker.regional['ru'] = {
	timeOnlyTitle: 'Выберите время',
	timeText: 'Время',
	hourText: 'Часы',
	minuteText: 'Минуты',
	secondText: 'Секунды',
	millisecText: 'миллисекунды',
	currentText: 'Теперь',
	closeText: 'Закрыть',
	ampm: false
};
$.timepicker.setDefaults($.timepicker.regional['ru']);