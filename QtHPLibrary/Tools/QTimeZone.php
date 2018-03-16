<?php

/**
 * Description of DTimeZone
 *
 * @author rbala
 * @todo : Iana timzone parsing has not been implemented yet,
 *         this class uses PHP DateTime and Transition for now
 *         Timezone limits are from 12/13/1901 20:45:52 to 1/19/2038 3:14:07
 */
class QTimeZone extends QAbstractObject {
        const
          TzAfricaAbidjan               = 'Africa/Abidjan',
          TzAfricaAccra                 = 'Africa/Accra',
          TzAfricaAddisAbaba            = 'Africa/Addis_Ababa',
          TzAfricaAlgiers               = 'Africa/Algiers',
          TzAfricaAsmara                = 'Africa/Asmara',
          TzAfricaBamako                = 'Africa/Bamako',
          TzAfricaBangui                = 'Africa/Bangui',
          TzAfricaBanjul                = 'Africa/Banjul',
          TzAfricaBissau                = 'Africa/Bissau',
          TzAfricaBlantyre              = 'Africa/Blantyre',
          TzAfricaBrazzaville           = 'Africa/Brazzaville',
          TzAfricaBujumbura             = 'Africa/Bujumbura',
          TzAfricaCairo                 = 'Africa/Cairo',
          TzAfricaCasablanca            = 'Africa/Casablanca',
          TzAfricaCeuta                 = 'Africa/Ceuta',
          TzAfricaConakry               = 'Africa/Conakry',
          TzAfricaDakar                 = 'Africa/Dakar',
          TzAfricaDarEsSalaam           = 'Africa/Dar_es_Salaam',
          TzAfricaDjibouti              = 'Africa/Djibouti',
          TzAfricaDouala                = 'Africa/Douala',
          TzAfricaElAaiun               = 'Africa/El_Aaiun',
          TzAfricaFreetown              = 'Africa/Freetown',
          TzAfricaGaborone              = 'Africa/Gaborone',
          TzAfricaHarare                = 'Africa/Harare',
          TzAfricaJohannesburg          = 'Africa/Johannesburg',
          TzAfricaJuba                  = 'Africa/Juba',
          TzAfricaKampala               = 'Africa/Kampala',
          TzAfricaKhartoum              = 'Africa/Khartoum',
          TzAfricaKigali                = 'Africa/Kigali',
          TzAfricaKinshasa              = 'Africa/Kinshasa',
          TzAfricaLagos                 = 'Africa/Lagos',
          TzAfricaLibreville            = 'Africa/Libreville',
          TzAfricaLome                  = 'Africa/Lome',
          TzAfricaLuanda                = 'Africa/Luanda',
          TzAfricaLubumbashi            = 'Africa/Lubumbashi',
          TzAfricaLusaka                = 'Africa/Lusaka',
          TzAfricaMalabo                = 'Africa/Malabo',
          TzAfricaMaputo                = 'Africa/Maputo',
          TzAfricaMaseru                = 'Africa/Maseru',
          TzAfricaMbabane               = 'Africa/Mbabane',
          TzAfricaMogadishu             = 'Africa/Mogadishu',
          TzAfricaMonrovia              = 'Africa/Monrovia',
          TzAfricaNairobi               = 'Africa/Nairobi',
          TzAfricaNdjamena              = 'Africa/Ndjamena',
          TzAfricaNiamey                = 'Africa/Niamey',
          TzAfricaNouakchott            = 'Africa/Nouakchott',
          TzAfricaOuagadougou           = 'Africa/Ouagadougou',
          TzAfricaPortoNovo             = 'Africa/Porto-Novo',
          TzAfricaSaoTome               = 'Africa/Sao_Tome',
          TzAfricaTripoli               = 'Africa/Tripoli',
          TzAfricaTunis                 = 'Africa/Tunis',
          TzAfricaWindhoek              = 'Africa/Windhoek',
          TzAmericaAdak                 = 'America/Adak',
          TzAmericaAnchorage            = 'America/Anchorage',
          TzAmericaAnguilla             = 'America/Anguilla',
          TzAmericaAntigua              = 'America/Antigua',
          TzAmericaAraguaina            = 'America/Araguaina',
          TzAmericaArgentinaBuenosAires = 'America/Argentina/Buenos_Aires',
          TzAmericaArgentinaCatamarca   = 'America/Argentina/Catamarca',
          TzAmericaArgentinaCordoba     = 'America/Argentina/Cordoba',
          TzAmericaArgentinaJujuy       = 'America/Argentina/Jujuy',
          TzAmericaArgentinaLaRioja     = 'America/Argentina/La_Rioja',
          TzAmericaArgentinaMendoza     = 'America/Argentina/Mendoza',
          TzAmericaArgentinaRioGallegos = 'America/Argentina/Rio_Gallegos',
          TzAmericaArgentinaSalta       = 'America/Argentina/Salta',
          TzAmericaArgentinaSanJuan     = 'America/Argentina/San_Juan',
          TzAmericaArgentinaSanLuis     = 'America/Argentina/San_Luis',
          TzAmericaArgentinaTucuman     = 'America/Argentina/Tucuman',
          TzAmericaArgentinaUshuaia     = 'America/Argentina/Ushuaia',
          TzAmericaAruba                = 'America/Aruba',
          TzAmericaAsuncion             = 'America/Asuncion',
          TzAmericaAtikokan             = 'America/Atikokan',
          TzAmericaBahia                = 'America/Bahia',
          TzAmericaBahiaBanderas        = 'America/Bahia_Banderas',
          TzAmericaBarbados             = 'America/Barbados',
          TzAmericaBelem                = 'America/Belem',
          TzAmericaBelize               = 'America/Belize',
          TzAmericaBlancSablon          = 'America/Blanc-Sablon',
          TzAmericaBoaVista             = 'America/Boa_Vista',
          TzAmericaBogota               = 'America/Bogota',
          TzAmericaBoise                = 'America/Boise',
          TzAmericaCambridgeBay         = 'America/Cambridge_Bay',
          TzAmericaCampoGrande          = 'America/Campo_Grande',
          TzAmericaCancun               = 'America/Cancun',
          TzAmericaCaracas              = 'America/Caracas',
          TzAmericaCayenne              = 'America/Cayenne',
          TzAmericaCayman               = 'America/Cayman',
          TzAmericaChicago              = 'America/Chicago',
          TzAmericaChihuahua            = 'America/Chihuahua',
          TzAmericaCostaRica            = 'America/Costa_Rica',
          TzAmericaCreston              = 'America/Creston',
          TzAmericaCuiaba               = 'America/Cuiaba',
          TzAmericaCuracao              = 'America/Curacao',
          TzAmericaDanmarkshavn         = 'America/Danmarkshavn',
          TzAmericaDawson               = 'America/Dawson',
          TzAmericaDawsonCreek          = 'America/Dawson_Creek',
          TzAmericaDenver               = 'America/Denver',
          TzAmericaDetroit              = 'America/Detroit',
          TzAmericaDominica             = 'America/Dominica',
          TzAmericaEdmonton             = 'America/Edmonton',
          TzAmericaEirunepe             = 'America/Eirunepe',
          TzAmericaElSalvador           = 'America/El_Salvador',
          TzAmericaFortaleza            = 'America/Fortaleza',
          TzAmericaGlaceBay             = 'America/Glace_Bay',
          TzAmericaGodthab              = 'America/Godthab',
          TzAmericaGooseBay             = 'America/Goose_Bay',
          TzAmericaGrandTurk            = 'America/Grand_Turk',
          TzAmericaGrenada              = 'America/Grenada',
          TzAmericaGuadeloupe           = 'America/Guadeloupe',
          TzAmericaGuatemala            = 'America/Guatemala',
          TzAmericaGuayaquil            = 'America/Guayaquil',
          TzAmericaGuyana               = 'America/Guyana',
          TzAmericaHalifax              = 'America/Halifax',
          TzAmericaHavana               = 'America/Havana',
          TzAmericaHermosillo           = 'America/Hermosillo',
          TzAmericaIndianaIndianapolis  = 'America/Indiana/Indianapolis',
          TzAmericaIndianaKnox          = 'America/Indiana/Knox',
          TzAmericaIndianaMarengo       = 'America/Indiana/Marengo',
          TzAmericaIndianaPetersburg    = 'America/Indiana/Petersburg',
          TzAmericaIndianaTellCity      = 'America/Indiana/Tell_City',
          TzAmericaIndianaVevay         = 'America/Indiana/Vevay',
          TzAmericaIndianaVincennes     = 'America/Indiana/Vincennes',
          TzAmericaIndianaWinamac       = 'America/Indiana/Winamac',
          TzAmericaInuvik               = 'America/Inuvik',
          TzAmericaIqaluit              = 'America/Iqaluit',
          TzAmericaJamaica              = 'America/Jamaica',
          TzAmericaJuneau               = 'America/Juneau',
          TzAmericaKentuckyLouisville   = 'America/Kentucky/Louisville',
          TzAmericaKentuckyMonticello   = 'America/Kentucky/Monticello',
          TzAmericaKralendijk           = 'America/Kralendijk',
          TzAmericaLaPaz                = 'America/La_Paz',
          TzAmericaLima                 = 'America/Lima',
          TzAmericaLosAngeles           = 'America/Los_Angeles',
          TzAmericaLowerPrinces         = 'America/Lower_Princes',
          TzAmericaMaceio               = 'America/Maceio',
          TzAmericaManagua              = 'America/Managua',
          TzAmericaManaus               = 'America/Manaus',
          TzAmericaMarigot              = 'America/Marigot',
          TzAmericaMartinique           = 'America/Martinique',
          TzAmericaMatamoros            = 'America/Matamoros',
          TzAmericaMazatlan             = 'America/Mazatlan',
          TzAmericaMenominee            = 'America/Menominee',
          TzAmericaMerida               = 'America/Merida',
          TzAmericaMetlakatla           = 'America/Metlakatla',
          TzAmericaMexicoCity           = 'America/Mexico_City',
          TzAmericaMiquelon             = 'America/Miquelon',
          TzAmericaMoncton              = 'America/Moncton',
          TzAmericaMonterrey            = 'America/Monterrey',
          TzAmericaMontevideo           = 'America/Montevideo',
          TzAmericaMontserrat           = 'America/Montserrat',
          TzAmericaNassau               = 'America/Nassau',
          TzAmericaNewYork              = 'America/New_York',
          TzAmericaNipigon              = 'America/Nipigon',
          TzAmericaNome                 = 'America/Nome',
          TzAmericaNoronha              = 'America/Noronha',
          TzAmericaNorthDakotaBeulah    = 'America/North_Dakota/Beulah',
          TzAmericaNorthDakotaCenter    = 'America/North_Dakota/Center',
          TzAmericaNorthDakotaNewSalem  = 'America/North_Dakota/New_Salem',
          TzAmericaOjinaga              = 'America/Ojinaga',
          TzAmericaPanama               = 'America/Panama',
          TzAmericaPangnirtung          = 'America/Pangnirtung',
          TzAmericaParamaribo           = 'America/Paramaribo',
          TzAmericaPhoenix              = 'America/Phoenix',
          TzAmericaPortAuPrince         = 'America/Port-au-Prince',
          TzAmericaPortOfSpain          = 'America/Port_of_Spain',
          TzAmericaPortoVelho           = 'America/Porto_Velho',
          TzAmericaPuertoRico           = 'America/Puerto_Rico',
          TzAmericaRainyRiver           = 'America/Rainy_River',
          TzAmericaRankinInlet          = 'America/Rankin_Inlet',
          TzAmericaRecife               = 'America/Recife',
          TzAmericaRegina               = 'America/Regina',
          TzAmericaResolute             = 'America/Resolute',
          TzAmericaRioBranco            = 'America/Rio_Branco',
          TzAmericaSantaIsabel          = 'America/Santa_Isabel',
          TzAmericaSantarem             = 'America/Santarem',
          TzAmericaSantiago             = 'America/Santiago',
          TzAmericaSantoDomingo         = 'America/Santo_Domingo',
          TzAmericaSaoPaulo             = 'America/Sao_Paulo',
          TzAmericaScoresbysund         = 'America/Scoresbysund',
          TzAmericaSitka                = 'America/Sitka',
          TzAmericaStBarthelemy         = 'America/St_Barthelemy',
          TzAmericaStJohns              = 'America/St_Johns',
          TzAmericaStKitts              = 'America/St_Kitts',
          TzAmericaStLucia              = 'America/St_Lucia',
          TzAmericaStThomas             = 'America/St_Thomas',
          TzAmericaStVincent            = 'America/St_Vincent',
          TzAmericaSwiftCurrent         = 'America/Swift_Current',
          TzAmericaTegucigalpa          = 'America/Tegucigalpa',
          TzAmericaThule                = 'America/Thule',
          TzAmericaThunderBay           = 'America/Thunder_Bay',
          TzAmericaTijuana              = 'America/Tijuana',
          TzAmericaToronto              = 'America/Toronto',
          TzAmericaTortola              = 'America/Tortola',
          TzAmericaVancouver            = 'America/Vancouver',
          TzAmericaWhitehorse           = 'America/Whitehorse',
          TzAmericaWinnipeg             = 'America/Winnipeg',
          TzAmericaYakutat              = 'America/Yakutat',
          TzAmericaYellowknife          = 'America/Yellowknife',
          TzAntarcticaCasey             = 'Antarctica/Casey',
          TzAntarcticaDavis             = 'Antarctica/Davis',
          TzAntarcticaDumontdurville    = 'Antarctica/DumontDUrville',
          TzAntarcticaMacquarie         = 'Antarctica/Macquarie',
          TzAntarcticaMawson            = 'Antarctica/Mawson',
          TzAntarcticaMcmurdo           = 'Antarctica/McMurdo',
          TzAntarcticaPalmer            = 'Antarctica/Palmer',
          TzAntarcticaRothera           = 'Antarctica/Rothera',
          TzAntarcticaSyowa             = 'Antarctica/Syowa',
          TzAntarcticaTroll             = 'Antarctica/Troll',
          TzAntarcticaVostok            = 'Antarctica/Vostok',
          TzArcticLongyearbyen          = 'Arctic/Longyearbyen',
          TzAsiaAden                    = 'Asia/Aden',
          TzAsiaAlmaty                  = 'Asia/Almaty',
          TzAsiaAmman                   = 'Asia/Amman',
          TzAsiaAnadyr                  = 'Asia/Anadyr',
          TzAsiaAqtau                   = 'Asia/Aqtau',
          TzAsiaAqtobe                  = 'Asia/Aqtobe',
          TzAsiaAshgabat                = 'Asia/Ashgabat',
          TzAsiaBaghdad                 = 'Asia/Baghdad',
          TzAsiaBahrain                 = 'Asia/Bahrain',
          TzAsiaBaku                    = 'Asia/Baku',
          TzAsiaBangkok                 = 'Asia/Bangkok',
          TzAsiaBeirut                  = 'Asia/Beirut',
          TzAsiaBishkek                 = 'Asia/Bishkek',
          TzAsiaBrunei                  = 'Asia/Brunei',
          TzAsiaChita                   = 'Asia/Chita',
          TzAsiaChoibalsan              = 'Asia/Choibalsan',
          TzAsiaColombo                 = 'Asia/Colombo',
          TzAsiaDamascus                = 'Asia/Damascus',
          TzAsiaDhaka                   = 'Asia/Dhaka',
          TzAsiaDili                    = 'Asia/Dili',
          TzAsiaDubai                   = 'Asia/Dubai',
          TzAsiaDushanbe                = 'Asia/Dushanbe',
          TzAsiaGaza                    = 'Asia/Gaza',
          TzAsiaHebron                  = 'Asia/Hebron',
          TzAsiaHoChiMinh               = 'Asia/Ho_Chi_Minh',
          TzAsiaHongKong                = 'Asia/Hong_Kong',
          TzAsiaHovd                    = 'Asia/Hovd',
          TzAsiaIrkutsk                 = 'Asia/Irkutsk',
          TzAsiaJakarta                 = 'Asia/Jakarta',
          TzAsiaJayapura                = 'Asia/Jayapura',
          TzAsiaJerusalem               = 'Asia/Jerusalem',
          TzAsiaKabul                   = 'Asia/Kabul',
          TzAsiaKamchatka               = 'Asia/Kamchatka',
          TzAsiaKarachi                 = 'Asia/Karachi',
          TzAsiaKathmandu               = 'Asia/Kathmandu',
          TzAsiaKhandyga                = 'Asia/Khandyga',
          TzAsiaKolkata                 = 'Asia/Kolkata',
          TzAsiaKrasnoyarsk             = 'Asia/Krasnoyarsk',
          TzAsiaKualaLumpur             = 'Asia/Kuala_Lumpur',
          TzAsiaKuching                 = 'Asia/Kuching',
          TzAsiaKuwait                  = 'Asia/Kuwait',
          TzAsiaMacau                   = 'Asia/Macau',
          TzAsiaMagadan                 = 'Asia/Magadan',
          TzAsiaMakassar                = 'Asia/Makassar',
          TzAsiaManila                  = 'Asia/Manila',
          TzAsiaMuscat                  = 'Asia/Muscat',
          TzAsiaNicosia                 = 'Asia/Nicosia',
          TzAsiaNovokuznetsk            = 'Asia/Novokuznetsk',
          TzAsiaNovosibirsk             = 'Asia/Novosibirsk',
          TzAsiaOmsk                    = 'Asia/Omsk',
          TzAsiaOral                    = 'Asia/Oral',
          TzAsiaPhnomPenh               = 'Asia/Phnom_Penh',
          TzAsiaPontianak               = 'Asia/Pontianak',
          TzAsiaPyongyang               = 'Asia/Pyongyang',
          TzAsiaQatar                   = 'Asia/Qatar',
          TzAsiaQyzylorda               = 'Asia/Qyzylorda',
          TzAsiaRangoon                 = 'Asia/Rangoon',
          TzAsiaRiyadh                  = 'Asia/Riyadh',
          TzAsiaSakhalin                = 'Asia/Sakhalin',
          TzAsiaSamarkand               = 'Asia/Samarkand',
          TzAsiaSeoul                   = 'Asia/Seoul',
          TzAsiaShanghai                = 'Asia/Shanghai',
          TzAsiaSingapore               = 'Asia/Singapore',
          TzAsiaSrednekolymsk           = 'Asia/Srednekolymsk',
          TzAsiaTaipei                  = 'Asia/Taipei',
          TzAsiaTashkent                = 'Asia/Tashkent',
          TzAsiaTbilisi                 = 'Asia/Tbilisi',
          TzAsiaTehran                  = 'Asia/Tehran',
          TzAsiaThimphu                 = 'Asia/Thimphu',
          TzAsiaTokyo                   = 'Asia/Tokyo',
          TzAsiaUlaanbaatar             = 'Asia/Ulaanbaatar',
          TzAsiaUrumqi                  = 'Asia/Urumqi',
          TzAsiaUstNera                 = 'Asia/Ust-Nera',
          TzAsiaVientiane               = 'Asia/Vientiane',
          TzAsiaVladivostok             = 'Asia/Vladivostok',
          TzAsiaYakutsk                 = 'Asia/Yakutsk',
          TzAsiaYekaterinburg           = 'Asia/Yekaterinburg',
          TzAsiaYerevan                 = 'Asia/Yerevan',
          TzAtlanticAzores              = 'Atlantic/Azores',
          TzAtlanticBermuda             = 'Atlantic/Bermuda',
          TzAtlanticCanary              = 'Atlantic/Canary',
          TzAtlanticCapeVerde           = 'Atlantic/Cape_Verde',
          TzAtlanticFaroe               = 'Atlantic/Faroe',
          TzAtlanticMadeira             = 'Atlantic/Madeira',
          TzAtlanticReykjavik           = 'Atlantic/Reykjavik',
          TzAtlanticSouthGeorgia        = 'Atlantic/South_Georgia',
          TzAtlanticStHelena            = 'Atlantic/St_Helena',
          TzAtlanticStanley             = 'Atlantic/Stanley',
          TzAustraliaAdelaide           = 'Australia/Adelaide',
          TzAustraliaBrisbane           = 'Australia/Brisbane',
          TzAustraliaBrokenHill         = 'Australia/Broken_Hill',
          TzAustraliaCurrie             = 'Australia/Currie',
          TzAustraliaDarwin             = 'Australia/Darwin',
          TzAustraliaEucla              = 'Australia/Eucla',
          TzAustraliaHobart             = 'Australia/Hobart',
          TzAustraliaLindeman           = 'Australia/Lindeman',
          TzAustraliaLordHowe           = 'Australia/Lord_Howe',
          TzAustraliaMelbourne          = 'Australia/Melbourne',
          TzAustraliaPerth              = 'Australia/Perth',
          TzAustraliaSydney             = 'Australia/Sydney',
          TzEuropeAmsterdam             = 'Europe/Amsterdam',
          TzEuropeAndorra               = 'Europe/Andorra',
          TzEuropeAthens                = 'Europe/Athens',
          TzEuropeBelgrade              = 'Europe/Belgrade',
          TzEuropeBerlin                = 'Europe/Berlin',
          TzEuropeBratislava            = 'Europe/Bratislava',
          TzEuropeBrussels              = 'Europe/Brussels',
          TzEuropeBucharest             = 'Europe/Bucharest',
          TzEuropeBudapest              = 'Europe/Budapest',
          TzEuropeBusingen              = 'Europe/Busingen',
          TzEuropeChisinau              = 'Europe/Chisinau',
          TzEuropeCopenhagen            = 'Europe/Copenhagen',
          TzEuropeDublin                = 'Europe/Dublin',
          TzEuropeGibraltar             = 'Europe/Gibraltar',
          TzEuropeGuernsey              = 'Europe/Guernsey',
          TzEuropeHelsinki              = 'Europe/Helsinki',
          TzEuropeIsleOfMan             = 'Europe/Isle_of_Man',
          TzEuropeIstanbul              = 'Europe/Istanbul',
          TzEuropeJersey                = 'Europe/Jersey',
          TzEuropeKaliningrad           = 'Europe/Kaliningrad',
          TzEuropeKiev                  = 'Europe/Kiev',
          TzEuropeLisbon                = 'Europe/Lisbon',
          TzEuropeLjubljana             = 'Europe/Ljubljana',
          TzEuropeLondon                = 'Europe/London',
          TzEuropeLuxembourg            = 'Europe/Luxembourg',
          TzEuropeMadrid                = 'Europe/Madrid',
          TzEuropeMalta                 = 'Europe/Malta',
          TzEuropeMariehamn             = 'Europe/Mariehamn',
          TzEuropeMinsk                 = 'Europe/Minsk',
          TzEuropeMonaco                = 'Europe/Monaco',
          TzEuropeMoscow                = 'Europe/Moscow',
          TzEuropeOslo                  = 'Europe/Oslo',
          TzEuropeParis                 = 'Europe/Paris',
          TzEuropePodgorica             = 'Europe/Podgorica',
          TzEuropePrague                = 'Europe/Prague',
          TzEuropeRiga                  = 'Europe/Riga',
          TzEuropeRome                  = 'Europe/Rome',
          TzEuropeSamara                = 'Europe/Samara',
          TzEuropeSanMarino             = 'Europe/San_Marino',
          TzEuropeSarajevo              = 'Europe/Sarajevo',
          TzEuropeSimferopol            = 'Europe/Simferopol',
          TzEuropeSkopje                = 'Europe/Skopje',
          TzEuropeSofia                 = 'Europe/Sofia',
          TzEuropeStockholm             = 'Europe/Stockholm',
          TzEuropeTallinn               = 'Europe/Tallinn',
          TzEuropeTirane                = 'Europe/Tirane',
          TzEuropeUzhgorod              = 'Europe/Uzhgorod',
          TzEuropeVaduz                 = 'Europe/Vaduz',
          TzEuropeVatican               = 'Europe/Vatican',
          TzEuropeVienna                = 'Europe/Vienna',
          TzEuropeVilnius               = 'Europe/Vilnius',
          TzEuropeVolgograd             = 'Europe/Volgograd',
          TzEuropeWarsaw                = 'Europe/Warsaw',
          TzEuropeZagreb                = 'Europe/Zagreb',
          TzEuropeZaporozhye            = 'Europe/Zaporozhye',
          TzEuropeZurich                = 'Europe/Zurich',
          TzIndianAntananarivo          = 'Indian/Antananarivo',
          TzIndianChagos                = 'Indian/Chagos',
          TzIndianChristmas             = 'Indian/Christmas',
          TzIndianCocos                 = 'Indian/Cocos',
          TzIndianComoro                = 'Indian/Comoro',
          TzIndianKerguelen             = 'Indian/Kerguelen',
          TzIndianMahe                  = 'Indian/Mahe',
          TzIndianMaldives              = 'Indian/Maldives',
          TzIndianMauritius             = 'Indian/Mauritius',
          TzIndianMayotte               = 'Indian/Mayotte',
          TzIndianReunion               = 'Indian/Reunion',
          TzPacificApia                 = 'Pacific/Apia',
          TzPacificAuckland             = 'Pacific/Auckland',
          TzPacificChatham              = 'Pacific/Chatham',
          TzPacificChuuk                = 'Pacific/Chuuk',
          TzPacificEaster               = 'Pacific/Easter',
          TzPacificEfate                = 'Pacific/Efate',
          TzPacificEnderbury            = 'Pacific/Enderbury',
          TzPacificFakaofo              = 'Pacific/Fakaofo',
          TzPacificFiji                 = 'Pacific/Fiji',
          TzPacificFunafuti             = 'Pacific/Funafuti',
          TzPacificGalapagos            = 'Pacific/Galapagos',
          TzPacificGambier              = 'Pacific/Gambier',
          TzPacificGuadalcanal          = 'Pacific/Guadalcanal',
          TzPacificGuam                 = 'Pacific/Guam',
          TzPacificHonolulu             = 'Pacific/Honolulu',
          TzPacificJohnston             = 'Pacific/Johnston',
          TzPacificKiritimati           = 'Pacific/Kiritimati',
          TzPacificKosrae               = 'Pacific/Kosrae',
          TzPacificKwajalein            = 'Pacific/Kwajalein',
          TzPacificMajuro               = 'Pacific/Majuro',
          TzPacificMarquesas            = 'Pacific/Marquesas',
          TzPacificMidway               = 'Pacific/Midway',
          TzPacificNauru                = 'Pacific/Nauru',
          TzPacificNiue                 = 'Pacific/Niue',
          TzPacificNorfolk              = 'Pacific/Norfolk',
          TzPacificNoumea               = 'Pacific/Noumea',
          TzPacificPagoPago             = 'Pacific/Pago_Pago',
          TzPacificPalau                = 'Pacific/Palau',
          TzPacificPitcairn             = 'Pacific/Pitcairn',
          TzPacificPohnpei              = 'Pacific/Pohnpei',
          TzPacificPortMoresby          = 'Pacific/Port_Moresby',
          TzPacificRarotonga            = 'Pacific/Rarotonga',
          TzPacificSaipan               = 'Pacific/Saipan',
          TzPacificTahiti               = 'Pacific/Tahiti',
          TzPacificTarawa               = 'Pacific/Tarawa',
          TzPacificTongatapu            = 'Pacific/Tongatapu',
          TzPacificWake                 = 'Pacific/Wake',
          TzPacificWallis               = 'Pacific/Wallis',
          TzUtc                         = 'UTC',
          TzLocalTime                   = 'LTC';

    private static $_dtz = null;
    const TimeZoneBirth = -2147483648,
          TimeZoneDeath = 2147483647;

    private $_tz = null;

    public function __construct($tz){
        if($tz === self::TzLocalTime){
            $tz = self::timezone();
        }
        if(!is_string($tz)){
            $fga = func_get_args();
            throw new QTimeZoneSignatureException('Call to undefined function ' . __CLASS__ . '::' . __METHOD__ . '(' . implode(',', array_map('dpsGetType', $fga)) . ')');
        }
        if(!self::isTimeZoneAvailable($tz)){
            throw new QTimeZoneIdException('"' . $tz . '" is not a valid time zone');
        }
        $this->_tz = $tz;
    }

    public function hasDaylightTime(){
        if($tz === self::TzLocalTime){
            $tz = self::timezone();
        }
        if($tz !== null && !self::isTimeZoneAvailable($tz)){
            throw new QTimeZoneIdException('"' . $tz . '" is not a valid time zone');
        }
        $tz = $tz ? $tz : self::timezone();
        $t = $dt->toTimestamp();
        if($t > self::TimeZoneBirth && $t < self::TimeZoneDeath){
            $tzts = timezone_transitions_get(new QateTimeZone($tz), $t, $t+86400*365);
            foreach($tzts as $tzt){
                if($tzt['isdst'])
                    return true;
            }
        }
        return false;
    }

    public function utcOffset($dt){
        return self::getUtcOffset($dt, $this->_tz);
    }

    public function daylightTimeOffset($dt){
        return self::getDaylightTimeOffset($dt, $this->_tz);
    }
    public function standardTimeOffset($dt){
        return self::getStandardTimeOffset($dt, $this->_tz);
    }

    public static function isTimeZoneAvailable($tz){
        if($tz === self::TzLocalTime){
            $tz = self::timezone();
        }
        // About 25 seconds faster than in_array($tz, timezone_identifiers_list()) for 100 000 iterations
        $r = new ReflectionClass('DTimeZone');
        return $r->getConstant('Tz' . implode('', array_map('ucfirst', array_map('strtolower', preg_split('/[\/_-]/', $tz))))) !== false;
    }

    // Difference from utc to standardTime
    public static function getUtcOffset($dt, $tz = null){
        if($tz === self::TzLocalTime){
            $tz = self::timezone();
        }
        if($tz !== null && !self::isTimeZoneAvailable($tz)){
            throw new QTimeZoneIdException('"' . $tz . '" is not a valid time zone');
        }
        $tz = $tz ? $tz : self::timezone();
        $t = ceil($dt->t()/1000);
        if($t < self::TimeZoneBirth || $t > self::TimeZoneDeath){
            return 0;
        }
        $tzt = self::_php52_timezone_transitions_get(new QateTimeZone($tz), $t, $t);
        return $tzt[0]['offset'];
    }

    /* Difference from dst to standardTimeOffset */
    public static function getDaylightTimeOffset($dt, $tz = null){
        if($tz === self::TzLocalTime){
            $tz = self::timezone();
        }
        if($tz === self::TzLocalTime){
            $tz = self::timezone();
        }
        if($tz !== null && !self::isTimeZoneAvailable($tz)){
            throw new QTimeZoneIdException('"' . $tz . '" is not a valid time zone');
        }
        $tz = $tz ? $tz : self::timezone();
        $t = $dt->toTimestamp();
        if($t < self::TimeZoneBirth || $t > self::TimeZoneDeath){
            return 0;
        }
        $tzts = self::_php52_timezone_transitions_get(new QateTimeZone($tz), $t, $t+86400*365);
        $dst = $sto = null;
        foreach($tzts as $tzt){
            if($tzt['isdst']){
                $dst = $tzt['offset'];
            } else {
                $sto = $tzt['offset'];
            }
            if($dst !== null && $sto !== null){
                break;
            }
        }
        return $dst !== null ? $dst - $sto : 0;
    }

    // Difference from utc
    public static function getStandardTimeOffset($dt, $tz = null){
        if($tz === self::TzLocalTime){
            $tz = self::timezone();
        }
        if($tz !== null && !self::isTimeZoneAvailable($tz)){
            throw new QTimeZoneIdException('"' . $tz . '" is not a valid time zone');
        }
        $tz = $tz ? $tz : self::timezone();
        $t = $dt->toTimestamp();
        if($t < self::TimeZoneBirth || $t > self::TimeZoneDeath){
            return 0;
        }
        $tzts = self::_php52_timezone_transitions_get(new QateTimeZone($tz), $t, $t+86400*365);
        foreach($tzts as $tzt){
            if(!$tzt['isdst']){
                return $tzt['offset'];
            }
        }
    }

    public static function setTimezone($tz){
        if($tz === self::TzLocalTime){return;}
        if(!is_string($tz)){
            $fga = func_get_args();
            throw new QTimeZoneException('Call to undefined function ' . __CLASS__ . '::' . __METHOD__  . '(' . implode(', ', array_map('gettype', $fga)) . ')');
        }
        if(!date_default_timezone_set($tz)){
            throw new QTimeZoneNameException('Invalid timezone "' . $tz . '"');
        }
    }

    public static function timezone(){
        return @date_default_timezone_get();
    }

    public function serverTimezone(){
        return self::$_dtz ? self::$_dtz : ((self::$_dtz = self::timezone()) ? self::$_dtz : self::TzUtc);
    }

    private static function _php52_timezone_transitions_get($dtz, $t0, $t1){
        $tzts = timezone_transitions_get($dtz);
        $_tzts = array();
        foreach($tzts as $k => $tzt){
            if($tzt['ts'] > $t0){
                if(!isset($_tzts[0]) && $k > 0){
                    $_tzts[0] = $tzts[$k-1];
                }
                $_tzts[] = $tzt;
            }
            if($tzt['ts'] > $t1){
                if(!count($_tzts)){
                    $_tzts[] = $tzt;
                }
                return $_tzts;
            }
        }
        return array($tzts[0]);
    }
}

class QTimeZoneException extends QAbstractObjectException {}
class QTimeZoneIdException extends QAbstractObjectException {}

// Initialize and prevent PHP's date warnings :)
QTimeZone::setTimezone(QTimeZone::serverTimezone());