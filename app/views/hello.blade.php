@extends('layouts.master')

@section('content')

<h2>Bibrex?</h2>
<p>
    Bibrex er et utlånssystem som
    bygger videre på to tråder; 
    <a href="http://instagram.com/p/Xcgf5MtMEW/">BIBCRAFT</a> (selvstendig utlånssystem)
    og <abbr title="Hadde vi ikke et bilde av dette også..?">dingseutlånet</abbr>, 
    og spinner inn en ny tråd:
    kommunikasjon med Bibsys over <a href="http://www.ncip.info/">NCIP</a>-protokollen. 
</p>

<p>
    Systemet kan i utgangspunktet håndtere <abbr title="Ikke utlån med REFID/HEFTID/INNID">nesten</abbr> alle utlån, men er mest meningsfullt å 
    bruke der bruker og/eller utlånsobjekt ikke finnes i BIBSYS, f.eks. for
    utlån til studenter som ikke enda er importert i BIBSYS og utlån 
    av dingser.
</p>

<p>
    Bibrex sjekker om bruker er i BIBSYS. Ved utlån av et BIBSYS-registrert 
    dokument til en bruker som ikke er i BIBSYS blir lånet registrert på en 
    forhåndsdefinert samlebruker («Midlertidig låner») i BIBSYS for å indikere 
    at dokumentet er utlånt – Den nødvendig informasjonen for å identifisere 
    låneren lagres istedet lokalt. Om brukeren (og dokumentet) derimot skulle 
    være i BIBSYS blir lånet registrert på vedkommende som normalt.
</p>

<h2>Enkel brukerveiledning</h2>
<ol>
    <li>
        I det første feltet skanner man kortnummer
        eller oppgir navn på låner.
        <ul>
            <li>
                Hvis brukeren har et student/ansattkort skanner vi alltid dette. 
            </li>
            <li>
                Hvis ikke skriver vi inn navn på formen «Etternavn, Fornavn».
            </li>
        </ul>

    </li>
    <li>
        Velg dings eller skann inn bokas DOKID/KNYTTID i det neste feltet ved 
        å legge boka på RFID-plata. Pass på at RFID-programmet står i utlånsmodus
        for at boka skal bli avalarmisert.
        Bibrex fikser dessverre ikke dette på egenhånd.
    </li>
    <li>
        Hvis brukeren ikke har lånt før og ikke finnes i BIBSYS vil Bibrex
        be deg om å fylle inn personopplysninger manuelt. Man kan også
        gå inn og redigere denne informasjonen i etterkant.
    </li>
</ol>

<h2>Hjelp!</h2>
<p>
    Bibrex er et eksperiment i produksjon. 
    Hvis du får en uforståelig feilmelding er det mest sannsynlig 
    <em>ikke</em> du som har gjort noe galt.
    <ol>
        <li>
            Skriv ned DOKID og nok informasjon til å identifisere låneren 
            (navn, LTID og/eller telefon/epost)
            på en lapp som du gir til Dan Michael. 
        </li>
        <li>
            Gi boka til låneren med et smil :)
        </li>
    </ol>
    Boka avalarmiseres med en gang du legger den på plata, så det
    bør ikke være noe problem.
</p>

<h2>Men hva med...?</h2>
<ul>
    <li>
        Reserveringer og bestillinger er ikke støttet. 
    </li>
    <li>
        HEFTID/INNID/REFID er ikke støttet.
    </li>
    <li>
        Hvis en bok utlånt i Bibrex blir returnert i Bibsys vil den
        også bli returnert i Bibrex. Det kan imidlertid være et visst
        etterslep.
    </li>
    <li>
        Det er ikke mulig å sende ut purringer (enda).
    <li>
        Andre ting? Spør Dan Michael
    </li>
</ul>

<h2>Se også</h2>
<ul>
    <li>
        <a href="https://www.ub.uio.no/for-ansatte/publikumstjenester/veiledning/ureal/oppslagstavle/2013/nye-studenter-og-kort.html">«Nye studenter og låning/kort», bloggpost 9. august 2013</a>
    </li>
    <li>
        <a href="https://www.ub.uio.no/for-ansatte/publikumstjenester/veiledning/ureal/oppslagstavle/2013/rex-spiser-dingser.html">«Rex har spist dingsene», bloggpost 13. august 2013</a>
    </li>
    <li>
        <a href="https://www.ub.uio.no/for-ansatte/innlaan-utlaan/utlaan/ureal/lanekort.html">«Lånekort», svært nyttig guide, oppdatert 9. aug 2013</a>
    </li>
    <li>
        <a href="https://www.ub.uio.no/for-ansatte/om-ubo/grupper/bibsysgrupper/doksok/studiekort-fs-feide.html">«Studiekort, FS, FEIDE», 22. jan 2010</a>
    </li>
</ul>

@stop


@section('scripts')

<script type='text/javascript'>     
  $(document).ready(function() {
    $('#ltid').focus();              
  });
</script>

@stop
