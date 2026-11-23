# Wedding

Aplikacja Laravel do przygotowania strony weselnej, którą można dostosować do pary młodej i jej wydarzenia. Goście znajdą na niej informacje o uroczystości, a w odpowiednim czasie będą mogli zaproponować piosenki i zabawy oraz przesłać zdjęcia. Administrator będzie zarządzać treścią i ustawieniami bez konieczności zmiany kodu.

## Status projektu

Pierwsze uruchomienie obejmuje konfigurację danych wesela, utworzenie administratora, logowanie i edycję ustawień oraz podstawową stronę dla gości. Pozostałe opisane poniżej funkcje, w tym zgłoszenia i integracja zdjęć, są planowane i nie zostały jeszcze wdrożone.

W katalogu [`.mockups`](.mockups/) znajdują się projekt strony, osobny wzór hero i zdjęcie przeznaczone do użycia w hero. Mockup pokazuje wygląd i przykładowe stany, ale sam nie realizuje logiki aplikacji.

## Strona dla gości

Strona ma być jednostronicowa, responsywna i dostępna na komputerach oraz telefonach. Górne menu na komputerze będzie poziome i przyklejone do górnej krawędzi. Na telefonie przyciski „Zaproponuj piosenkę” i „Prześlij zdjęcia” mają być widoczne na samej górze.

Oba przyciski pokazują dostępność funkcji: zielony oznacza funkcję aktywną, a szary funkcję, która jeszcze nie jest dostępna albo została zamknięta. Stan wynika z terminów zapisanych w bazie, a nie z samego wyglądu mockupu.

### Hero

- Zdjęcie z projektu na całą szerokość, imiona Diana i Łukasz oraz data 26 czerwca 2027.
- Wygląd na podstawie [wzoru hero](.mockups/index.htm).
- Przycisk „Poznaj szczegóły” przewija do sekcji „Gdzie i kiedy”.
- Bez licznika odliczającego do ślubu.

### Gdzie i kiedy

Dwie karty zawierają szczegóły wydarzenia i odnośniki otwierające odpowiednie lokalizacje w Google Maps:

- Ceremonia: Kościół Miłosierdzia Bożego, Białystok, godz. 16:30.
- Przyjęcie: Restauracja Belweder, Porosły-Kolonia, godz. 18:00.

Dokładne adresy i docelowe lokalizacje map wymagają uzupełnienia.

### Propozycje piosenek

Formularz pozwala podać tytuł piosenki i wykonawcę oraz opcjonalnie imię i dedykację lub komentarz. Po wysłaniu gość otrzymuje potwierdzenie.

Termin zamknięcia jest odczytywany z ustawień w bazie. Planowana wartość początkowa to 20 czerwca 2027; administrator będzie mógł ją zmienić. Do zamknięcia formularz jest widoczny, a przycisk w menu zielony. Po zamknięciu formularz zastępuje ciemnozielona informacja o przekazaniu listy DJ-owi i podziękowanie, a przycisk w menu staje się szary. Dokładna godzina zamknięcia i strefa czasowa wymagają ustalenia przy implementacji.

### Zdjęcia

Przed datą i godziną wesela zapisaną w ustawieniach sekcja pokazuje aparat oraz informację, że galeria i przesyłanie zdjęć będą dostępne od dnia wesela. Przycisk w menu jest szary.

Od ustawionego momentu przesyłanie włącza się automatycznie, a przycisk staje się zielony. Goście będą mogli wybrać wiele zdjęć, obejrzeć podgląd przed wysłaniem, opcjonalnie podać imię autora, obserwować postęp i otrzymać potwierdzenie. Nie będą potrzebowali konta Microsoft.

Planowana jest integracja z OneDrive przez backend albo zapis we własnym backendzie. Sposób uwierzytelniania OneDrive, ewentualne wyświetlanie galerii i termin zakończenia przesyłania pozostają do ustalenia. Niezależnie od wybranej integracji należy zapewnić walidację i limity plików, obsługę błędów oraz ochronę przed nadużyciami. Sekrety integracji pozostają po stronie serwera.

### Timeline znajomości

Oś przedstawia pięć wydarzeń: na komputerze poziomo, a poniżej 640 px pionowo z kropkami.

| Data | Wydarzenie |
| --- | --- |
| 26.06.2025 | Poznanie |
| 19.07.2025 | Pierwsza randka |
| 11.10.2025 | Początek związku |
| 12.09.2026 | Zaręczyny |
| 26.06.2027 | Ślub |

### Zabawy weselne

Gość może zgłosić nazwę zabawy, jej opis i zasady oraz opcjonalnie swoje imię. Formularz działa do terminu zapisanego w ustawieniach; planowana wartość początkowa to 19 czerwca 2027. Po tym terminie formularz znika.

Pod formularzem znajduje się sekcja „Zabawy na weselu”. Gdy nie ma opublikowanych zabaw, wyświetla informację „Wkrótce podamy, jakie zabawy nas czekają”. Po dodaniu zabaw nagłówek zmienia się na „Lista zabaw”, a każdą pozycję można rozwinąć, by przeczytać zasady. Zgłoszenie gościa nie powinno być automatycznie publikowane jako zatwierdzona zabawa.

### Menu weselne

Do czasu uzupełnienia danych widoczna jest informacja „Menu pojawi się tutaj później”. Gotowe menu będzie podzielone na kategorie: przystawki, zupy, dania główne, desery, tort i napoje.

### Harmonogram dnia

Pionowa oś czasu prezentuje ceremonię o 16:30 i przyjęcie o 18:00. Pierwszy taniec, tort i oczepiny mają początkowo oznaczenie „ok. --:--”; godziny będzie można później uzupełnić, np. „ok. 19:00”.

### FAQ

Pytania i odpowiedzi są rozwijane i podzielone na pięć grup:

- Dojazd i logistyka.
- Ślub i potwierdzenie obecności.
- Program.
- Na sali.
- Po weselu.

Odpowiedzi z mockupu zawierają warianty i placeholdery. Przed publikacją należy wybrać odpowiednie wersje i uzupełnić brakujące informacje; nie należy przedstawiać niepotwierdzonych szczegółów jako faktów.

## Ustawienia i panel administratora

Tabela `wedding_settings` przechowuje podstawowe dane oraz terminy zamknięcia zgłoszeń. Daty w formularzach podawane są w strefie Europe/Warsaw i zapisywane w UTC. Po wdrożeniu formularzy ich dostępność będzie sprawdzana po stronie serwera; termin zamknięcia jest momentem, od którego zgłoszenia nie będą już przyjmowane. Wartości w bazie są źródłem prawdy po inicjalizacji.

Panel administratora dostępny po zalogowaniu umożliwia edycję podstawowych ustawień, bez ich usuwania. Zarządzanie pozostałą treścią i zgłoszeniami powstanie w kolejnych etapach. Nie ma publicznej rejestracji ani domyślnego konta administratora.

## Konfiguracja przed wdrożeniem

1. Skonfiguruj bazę i klucz aplikacji, a następnie uruchom migracje. Przed migracją istniejącej bazy przygotuj kopię zapasową.
2. Ustaw `WEDDING_SETUP_SECRET` w środowisku na długi, losowy sekret i uruchom `php artisan config:clear`, jeśli konfiguracja była buforowana. Po zmianie zmiennych środowiska restartuj proces aplikacji, jeśli zachowuje je w pamięci. Na produkcji zapewnij HTTPS i ogranicz dostęp do instalacji do czasu jej ukończenia.
3. Otwórz `/setup`, podaj sekret, utwórz konto administratora i wprowadź wymagane dane. Adresy i linki do map są opcjonalne. Hasło musi mieć co najmniej 12 znaków.
4. Po konfiguracji usuń sekret ze środowiska i odśwież cache konfiguracji. `/setup` jest odtąd zamknięty. Ustawienia można zmieniać po zalogowaniu pod `/login`.

Jeśli sekret nie jest ustawiony, setup jest niedostępny. Próby setupu i logowania są ograniczane; na produkcji używaj współdzielonego, trwałego magazynu cache przy wielu instancjach aplikacji. Ponowne wdrożenie i migracje nie nadpisują zapisanych danych. Jeśli baza zawiera tylko użytkownika albo niekompletne ustawienia, konfiguracja nie jest automatycznie wznawiana: operator musi najpierw sprawdzić i naprawić stan bazy.

Sekrety, hasła i konfiguracja połączeń należą do środowiska produkcyjnego, nie do danych publikowanych w repozytorium. Przed wykonaniem migracji na istniejącej bazie należy przygotować kopię zapasową.

## Do uzupełnienia

- Dokładne adresy i odnośniki Google Maps obu miejsc.
- Godziny pozostałych punktów harmonogramu.
- Dania, zatwierdzone zabawy i ostateczne odpowiedzi FAQ.
- Dokładne godziny i strefa czasowa zamknięcia zgłoszeń oraz konfiguracja dostępności zdjęć.
- Szczegóły integracji i przechowywania zdjęć oraz konfiguracji produkcyjnej.

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
