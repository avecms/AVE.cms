// Slavík))) 02,2017 Olga "Un.Known" Andělová # https://www.facebook.com/olga.andelova

var logoutTitle = "východ z Ovládacích panelů";
var logoutConfirm = "Opravdu chcete odejít?";
var clearCacheTitle = "Smazání mezipaměti";
var clearCacheConfirm = "Opravdu chcete smazat mezipaměť?";
var clearCacheSessTitle = "Mazání mezipaměti a relací";
var clearCacheSessConfirm = "Opravdu chcete vymazat mezipaměť a relace?";
var clearThumbTitle = "Odstranit miniatury";
var clearThumbConfirm = "Opravdu chcete vymazat všechny miniatury zobrazení?<br/>z adresáře pro ukládání souborů (UPLOAD_DIR)?";
var clearRevTitle = "Smazat revizi dokumentů";
var clearRevConfirm = "Opravdu chcete vymazat všechny revize dokumentů?";
var clearCountTitle = "Resetovat denní počítadlo dokumentů";
var clearCountConfirm = "Opravdu chcete vynulovat denní počítadlo dokumentů?";
var cacheShowTitle = "Zobrazit velikost cache";
var cacheShowConfirm = "Jste si jisti, že chcete vidět velikost cache?<br />může to trvat nějaký čas.";
var ajaxErrorStatus = "Žádné spojení.<br />Zkontrolujte připojení.";
var ajaxErrorStatus404 = "Stránka, kterou hledáte nebyla nalezena. [404]";
var ajaxErrorStatus401 = "Požadavek nelze splnit.<br /> Chyba autorizaca pro vykonání požadavku [401]";
var ajaxErrorStatus500 = "Došlo k vnitřní chybě.<br />Zkuste to znovu později. [500]";
var ajaxErrorStatusJSON = "Špatná odpověď serveru<br />Data jsou ve špatném formátu JSON.";
var ajaxErrorStatusTimeOut = "Čas požadavku uplynul.";
var ajaxErrorStatusAbort = "Ajax požadavek přerušen.";
var ajaxErrorStatusMess = "Cyhba:<br />";
var delCascadTitle = "odstranit zobrazení;
var delCascadConfirm 
= "Opravdu chcete smazat?";
var saveMessageOk = "Data uložena";

//===== Date & Time Pickers =====//
$.datepicker.regional['cz'] = {
	closeText: 'Zavřít',
	prevText: '<Zpět',
	nextText: 'Vpřed>',
	currentText: 'Nyní',
	monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen',
		'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'
	],
	monthNamesShort: ['Led', 'Úno', 'Bře', 'Dub', 'Kvě', 'Čvn',
		'Čvc', 'Srp', 'Zář', 'Říj', 'Lis', 'Pro'
	],
	dayNames: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'],
	dayNamesShort: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
	dayNamesMin: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
	weekHeader: 'Ne',
	dateFormat: 'dd.mm.yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['cz']);

$.timepicker.regional['cz'] = {
	timeOnlyTitle: 'Vyberte čas',
	timeText: 'Čas',
	hourText: 'Hodiny',
	minuteText: 'Minuty',
	secondText: 'Sekundy',
	millisecText: 'milisekundy',
	currentText: 'Teď',
	closeText: 'Zavřít',
	ampm: false
};
$.timepicker.setDefaults($.timepicker.regional['cz']);