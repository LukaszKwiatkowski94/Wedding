# Wedding

Aplikacja Laravel do przygotowania strony weselnej, którą można dostosować do pary młodej i jej wydarzenia. Goście znajdą na niej informacje o uroczystości, a w odpowiednim czasie będą mogli zaproponować piosenki i zabawy oraz przesłać zdjęcia. Administrator będzie zarządzać treścią i ustawieniami bez konieczności zmiany kodu.

## Status projektu

Pierwsze uruchomienie obejmuje konfigurację danych wesela, utworzenie administratora, logowanie i edycję ustawień oraz stronę dla gości z formularzem propozycji piosenek. Pozostałe opisane poniżej funkcje, w tym zgłoszenia zabaw i integracja zdjęć, są planowane i nie zostały jeszcze wdrożone.

W katalogu [`.mockups`](.mockups/) znajdują się projekt strony, osobny wzór hero i zdjęcie przeznaczone do użycia w hero. Mockup pokazuje wygląd i przykładowe stany, ale sam nie realizuje logiki aplikacji.

## Strona dla gości

Strona ma być jednostronicowa, responsywna i dostępna na komputerach oraz telefonach. Górne menu na komputerze będzie poziome i przyklejone do górnej krawędzi. Na telefonie przyciski „Zaproponuj piosenkę” i „Prześlij zdjęcia” mają być widoczne na samej górze.

Oba przyciski pokazują dostępność funkcji: zielony oznacza funkcję aktywną, a szary funkcję, która jeszcze nie jest dostępna albo została zamknięta. Stan wynika z terminów zapisanych w bazie, a nie z samego wyglądu mockupu.

### Hero

- Zdjęcie z projektu na całą szerokość oraz imiona i data wesela wprowadzone w ustawieniach.
- Wygląd na podstawie [wzoru hero](.mockups/index.htm).
- Przycisk „Poznaj szczegóły” przewija do sekcji „Gdzie i kiedy”.
- Bez licznika odliczającego do ślubu.

### Gdzie i kiedy

Dwie karty przedstawiają daty, godziny i miejsca ceremonii oraz przyjęcia zapisane w ustawieniach. Jeśli administrator poda adres lub link do mapy, są one również widoczne na stronie.

Nazwy miejsc i miejscowości są wymagane podczas konfiguracji; dokładne adresy i linki do map są opcjonalne.

### Propozycje piosenek

Formularz pozwala podać tytuł piosenki i wykonawcę oraz opcjonalnie imię i dedykację lub komentarz. Po wysłaniu gość otrzymuje potwierdzenie.

Termin zamknięcia ustawia administrator. Do tego momentu formularz jest widoczny, a odnośnik w menu zielony. Po zamknięciu formularz zastępuje informacja o zakończeniu zgłoszeń i podziękowanie, a odnośnik w menu staje się szary. Termin jest sprawdzany na serwerze także przy wysyłaniu formularza. Zgłoszenia mogą się powtarzać.

### Zdjęcia

Przed datą i godziną wesela zapisaną w ustawieniach sekcja pokazuje aparat oraz informację, że galeria i przesyłanie zdjęć będą dostępne od dnia wesela. Przycisk w menu jest szary.

Od ustawionego momentu przesyłanie włącza się automatycznie, a przycisk staje się zielony. Goście będą mogli wybrać wiele zdjęć, obejrzeć podgląd przed wysłaniem, opcjonalnie podać imię autora, obserwować postęp i otrzymać potwierdzenie. Nie będą potrzebowali konta Microsoft.

Planowana jest integracja z OneDrive przez backend albo zapis we własnym backendzie. Sposób uwierzytelniania OneDrive, ewentualne wyświetlanie galerii i termin zakończenia przesyłania pozostają do ustalenia. Niezależnie od wybranej integracji należy zapewnić walidację i limity plików, obsługę błędów oraz ochronę przed nadużyciami. Sekrety integracji pozostają po stronie serwera.

### Timeline znajomości

Planowana oś przedstawi wydarzenia z historii pary z datami i opisami wprowadzanymi przez administratora. Na komputerze będzie pozioma, a poniżej 640 px pionowa z kropkami. Zarządzanie wydarzeniami nie zostało jeszcze wdrożone.

### Zabawy weselne

Gość będzie mógł zgłosić nazwę zabawy, jej opis i zasady oraz opcjonalnie swoje imię. Formularz będzie dostępny do terminu ustawionego przez administratora; po jego upływie zniknie.

Pod formularzem znajduje się sekcja „Zabawy na weselu”. Gdy nie ma opublikowanych zabaw, wyświetla informację „Wkrótce podamy, jakie zabawy nas czekają”. Po dodaniu zabaw nagłówek zmienia się na „Lista zabaw”, a każdą pozycję można rozwinąć, by przeczytać zasady. Zgłoszenie gościa nie powinno być automatycznie publikowane jako zatwierdzona zabawa.

### Menu weselne

Do czasu uzupełnienia danych widoczna jest informacja „Menu pojawi się tutaj później”. Gotowe menu będzie podzielone na kategorie: przystawki, zupy, dania główne, desery, tort i napoje.

### Harmonogram dnia

Planowana pionowa oś czasu przedstawi ceremonię i przyjęcie na podstawie ustawień oraz pozostałe punkty dnia. Godziny dodatkowych punktów, takich jak pierwszy taniec, tort i oczepiny, będzie można później uzupełnić. Zarządzanie harmonogramem nie zostało jeszcze wdrożone.

### FAQ

Pytania i odpowiedzi są rozwijane i podzielone na pięć grup:

- Dojazd i logistyka.
- Ślub i potwierdzenie obecności.
- Program.
- Na sali.
- Po weselu.

Odpowiedzi z mockupu zawierają warianty i placeholdery. Przed publikacją należy wybrać odpowiednie wersje i uzupełnić brakujące informacje; nie należy przedstawiać niepotwierdzonych szczegółów jako faktów.

## Ustawienia i panel administratora

Tabela `wedding_settings` przechowuje podstawowe dane oraz terminy zamknięcia zgłoszeń. Daty w formularzach podawane są w strefie Europe/Warsaw i zapisywane w UTC. Termin zamknięcia jest momentem, od którego zgłoszenia nie są już przyjmowane. Wartości w bazie są źródłem prawdy po inicjalizacji.

Panel administratora dostępny po zalogowaniu umożliwia edycję podstawowych ustawień, przegląd propozycji piosenek oraz pobranie wszystkich zgłoszeń jako CSV. Usuwanie zgłoszeń ani automatyczne przekazanie ich DJ-owi nie jest dostępne. Zarządzanie pozostałą treścią powstanie w kolejnych etapach. Nie ma publicznej rejestracji ani domyślnego konta administratora.

## Konfiguracja przed wdrożeniem

1. Skonfiguruj bazę i klucz aplikacji, a następnie uruchom migracje. Przed migracją istniejącej bazy przygotuj kopię zapasową.
2. Ustaw `WEDDING_SETUP_SECRET` w środowisku na długi, losowy sekret i uruchom `php artisan config:clear`, jeśli konfiguracja była buforowana. Po zmianie zmiennych środowiska restartuj proces aplikacji, jeśli zachowuje je w pamięci. Na produkcji zapewnij HTTPS i ogranicz dostęp do instalacji do czasu jej ukończenia.
3. Otwórz `/setup`, podaj sekret, utwórz konto administratora i wprowadź wymagane dane. Adresy i linki do map są opcjonalne. Hasło musi mieć co najmniej 12 znaków.
4. Po konfiguracji usuń sekret ze środowiska i odśwież cache konfiguracji. `/setup` jest odtąd zamknięty. Ustawienia można zmieniać po zalogowaniu pod `/login`.

Jeśli sekret nie jest ustawiony, setup jest niedostępny. Próby setupu i logowania są ograniczane; na produkcji używaj współdzielonego, trwałego magazynu cache przy wielu instancjach aplikacji. Ponowne wdrożenie i migracje nie nadpisują zapisanych danych. Jeśli baza zawiera tylko użytkownika albo niekompletne ustawienia, konfiguracja nie jest automatycznie wznawiana: operator musi najpierw sprawdzić i naprawić stan bazy.

Sekrety, hasła i konfiguracja połączeń należą do środowiska produkcyjnego, nie do danych publikowanych w repozytorium. Przed wykonaniem migracji na istniejącej bazie należy przygotować kopię zapasową.

## Do uzupełnienia

- Opcjonalne adresy i linki do map dla miejsc wybranych przez administratora.
- Planowane zarządzanie historią pary, harmonogramem, menu, zabawami i FAQ.
- Konfiguracja dostępności zdjęć oraz szczegóły ich integracji i przechowywania.
- Konfiguracja środowiska produkcyjnego.

## Technologie

Obecny fundament wykorzystuje Laravel, PHP 8.3, MariaDB, Docker Compose i Vite. Zakres i technologie panelu administracyjnego oraz integracji OneDrive zostaną ustalone w kolejnych etapach.

## Uruchomienie lokalne

Otwórz projekt w Dev Container albo uruchom go przez Docker Compose:

```sh
docker compose -f compose.yaml -f .devcontainer/compose.build.yaml build workspace
docker compose up -d
docker compose exec workspace php artisan migrate
```

Aplikacja jest dostępna pod adresem http://localhost:8000. Przy pierwszym uruchomieniu kontener tworzy `.env` i klucz aplikacji, jeśli jeszcze nie istnieją. Przy każdym starcie instaluje zależności Composer i npm oraz buduje zasoby Vite. Migracje należy uruchamiać osobno.

Przydatne polecenia:

```sh
docker compose logs -f workspace
docker compose exec workspace php artisan migrate:status
docker compose exec workspace php artisan test
docker compose down
```

Testy używają bazy SQLite w pamięci, niezależnie od ustawień MariaDB w Compose. Dane MariaDB są przechowywane w wolumenie `wedding-db`; `docker compose down` ich nie usuwa.

## Konfiguracja

Plik `.env` jest lokalny i nie jest zapisywany w repozytorium. Zmienne środowiskowe ustawione w `compose.yaml` mają pierwszeństwo przed odpowiadającymi im wartościami w `.env`, w tym ustawieniami połączenia z bazą.

Konfiguracja Compose i hasła w niej zawarte są przeznaczone wyłącznie do lokalnego developmentu. Na produkcji należy osobno skonfigurować sekrety, bazę, adres aplikacji i serwer WWW oraz wyłączyć tryb debugowania. Migracje produkcyjne należy wykonywać jako osobny krok wdrożenia, po wykonaniu kopii zapasowej.
