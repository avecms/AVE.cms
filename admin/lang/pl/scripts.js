// Język polski 02,2017 duncan

var logoutTitle = "Wyjdz z Panelu sterowania";
var logoutConfirm = "Czy na pewno chcesz zakończyć?";
var clearCacheTitle = "Opróżnij pamięć podręczną";
var clearCacheConfirm = "Czy na pewno chcesz opróżnić pamięć podręczną?";
var clearCacheSessTitle = "Czyszczenie pamięci podręcznej i sesji";
var clearCacheSessConfirm = "Czy na pewno chcesz wyczyścić pamięć podręczną i sesji?";
var clearThumbTitle = "Usuń miniatury";
var clearThumbConfirm = "Czy na pewno chcesz usunąć wszystkie miniaturki obrazków <br/> z katalogu zapisywania plików (UPLOAD_DIR)?";
var clearRevTitle = "Usuń rewizji dokumentów";
var clearRevConfirm = "Czy na pewno chcesz usunąć wszystkie rewizje dokumentów?";
var clearCountTitle = "Wyzerować codzienny licznik dokumentów";
var clearCountConfirm = "Czy na pewno chcesz wyzerować codzienny licznik dokumentów?";
var cacheShowTitle = "Pokaż rozmiar pamięci podręcznej";
var cacheShowConfirm = "Czy na pewno chcesz sprawdzić rozmiar pamięci podręcznej <br /> może zająć trochę czasu.";
var ajaxErrorStatus = "Brak połączenia <br /> Sprawdź połączenie.";
var ajaxErrorStatus404 = "Strona, której szukasz nie została odnaleziona [404].";
var ajaxErrorStatus401 = "Żądanie nie może być spełnione <br /> Błąd autoryzacji dla spełnienia tego żądania [401]";
var ajaxErrorStatus500 = "Wystąpił błąd wewnętrzny <br /> Spróbuj ponownie później [500]";
var ajaxErrorStatusJSON = "Nieprawidłowa odpowiedź serwera <br /> Dane nie są w formacie JSON.";
var ajaxErrorStatusTimeOut = "Limit czasu zapytania.";
var ajaxErrorStatusAbort = "Żądanie Ajax przerwane.";
var ajaxErrorStatusMess = "Błąd: <br />";
var delCascadTitle = "Usuń obrazek";
var delCascadConfirm = "Czy na pewno chcesz usunąć?";
var saveMessageOk = "Dane zapisane";

//===== Date & Time Pickers =====//
$.datepicker.regional['pl'] = {
	closeText: 'Zamknij',
	prevText: '<Powrót',
	nextText: 'Dalej>',
	currentText: 'Dzisiaj',
	monthNames: ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec',
		'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'
	],
	monthNamesShort: ['Sty', 'Lut', 'Mar', 'Kvi', 'Maj', 'Sze',
		'Lip', 'Sie', 'Wrs', 'Paź', 'Lis', 'Gru'
	],
	dayNames: ['niedziela', 'poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek', 'sobota'],
	dayNamesShort: ['niedz', 'pon', 'wt', 'śr', 'czw', 'piąt', 'sob'],
	dayNamesMin: ['Ni', 'Pn', 'Wt', 'Sr', 'Cz', 'Pt', 'So'],
	weekHeader: 'Nie',
	dateFormat: 'dd.mm.yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['pl']);

$.timepicker.regional['pl'] = {
	timeOnlyTitle: 'Wybierz czas',
	timeText: 'Czas',
	hourText: 'Godziny',
	minuteText: 'Minuty',
	secondText: 'Sekundy',
	millisecText: 'Milisekundy',
	currentText: 'Teraz',
	closeText: 'Zamknij',
	ampm: false
};
$.timepicker.setDefaults($.timepicker.regional['pl']);