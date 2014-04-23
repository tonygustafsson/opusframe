TODO
----------------
* $this->load and so on, include on load.php and config.php or merging objects?
* Filtrering, färdiga efter datamodel.
* Pagination / Oändlig scroll
* Lost Seas lagrar userid från cookies i databasen som användarnamn, bra lösning? Automatisera? Cookies byter sessionsid efter några minuter.
* SEO module, generate sitemap?
* Debug module? Print valueble info to user, log events, measure times
* Ladda in css och js in i en vy, både i ajax och utan, placera den rätt
* Hur löser jag joins, checkboxar som representerar värden i en annan tabell? Behöver kunna filtreras
* Välja om du default vill ha ajax eller ej
* Backa i webbläsaren gå till förra sidan med html pushState

Pagination
----------
Vore bra att kunna länka till både en page och en sortering, sortering är dock sessionsstyrt idag. Hm.

Ska man kalla på paginationmodulen eller ska man styrai  pagiatnionmodulen vilka sidor som ska delas upp?

Ska pagination se ut på samma sätt, typ page_1/ i urlen? Var i URLen ska vilket värde finnas? Ska inte sånt sättas i URLen ändå?

Checkbox
Bockas den i så finns den, blir value="", annars finns den inte

Meddelanden
----------------
Ska köra med flash cookies
Om man sätter dem som _new_ när de sätts, och när de används sätter man om dem till _old_
Sedan gör man en clean på alla _old_ innan sidan laddas i __construct()

Start		Först rensar alla _current_, sedan sätter alla _next_ till _current_
			Då tar man inte bort de som är satta förra gången, utan de blir _current_
			och de förrförra raderas

More database info
----------------------
Kan kommas åt via $this->opus->database->db


Views
--------------
Om en vy ska kunna ladda andra views, så måste den veta vilka vyer, måste skickas in som parametrar. Bara krångligt.
Kanske bättre att bara slänga med header/footer om det inte är ajax
Man vill i vyn kunna välja om ajax ska styra utseende eller ej.
Vill inte ha det som en parameter in, så man kan kolla direkt i vyn
Då måste man kunna ladda andra vyer från en vy, ingen performance problem vad jag kan se
Vill inte ha ajaxkollen i ALAL vyer, bättre med en mastervy som bestämmer allt, med en inputparameter?
Varför bara ha en vy som argyument? Array med vyer som ska laddas i stället?
typ $view_modules = array('header', 'content', 'footer') så får man i mastervyn bestämma vart de ska placeras
alla får samma $data
Ska denna mstervy vara en shared view? då msåte arrayen peka på filerna som ska laddas in, header => header.shared, content => index
