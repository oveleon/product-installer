# Konfiguration
!> Wenn du noch nicht weißt, wie Bridges funktionieren oder wofür diese nötig sind, lese vorher [diesen Artikel](bridges/README.md)!

Die Konfiguration einer Bridge ist entscheidend, um die gewünschten Schritte und Prozesse im Produkt Installer zu definieren. Hier ist ein Beispielcode, der zeigt, wie die Konfiguration einer Bridge aussehen kann:

```php
use Oveleon\ProductInstaller\LicenseConnector\AbstractLicenseConnector;
use Oveleon\ProductInstaller\LicenseConnector\Process\ApiProcess;
use Oveleon\ProductInstaller\LicenseConnector\Process\ContaoManagerProcess;
use Oveleon\ProductInstaller\LicenseConnector\Process\DownloadProcess;
use Oveleon\ProductInstaller\LicenseConnector\Process\RegisterProductProcess;
use Oveleon\ProductInstaller\LicenseConnector\Step\AdvertisingStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\ContaoManagerStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\LicenseStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\ProductStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\ProcessStep;

class ThemeManagerLicenseConnector extends AbstractLicenseConnector
{
    function setSteps(): void
    {
       
        // Hinzufügen von Schritten, welche der Produkt Installer für die
        // Registrierung und Installation von Produkten durchzuführen hat. 
        $this->addSteps(
        
            // Der Lizenzschritt liefert eine Eingabemaske, in der der
            // Anwender seine Lizenz hinterlegen und prüfen kann. 
            new LicenseStep(),

            // Der Produktschritt zeigt nach erfolgreicher Lizenzabfrage
            // das zu installierende Produkt.
            new ProductStep(),

            // Der Werbeschritt, dient dazu, den Anwendern verschiedene
            // Mitteilungen / Werbung einspielen zu können. 
            new AdvertisingStep(),

            // Der Contao Manager-Schritt, ist notwendig, wenn Produkte
            // Abhängigkeiten beinhalten und über Composer installiert 
            // oder als Contao-Manager Artefakt installiert werden sollen.
            // In diesem Schritt wird die Verbindung zum Contao-Manager
            // geprüft und wenn nötig hergestellt.
            new ContaoManagerStep(),

            // Der Installationsprozess-Schritt bildet die eigentliche
            // Installation und die dazu benötigten Prozesse ab.
            (new ProcessStep())
                ->addProcesses(
                
                    // Der Download-Prozess ermöglicht das Herunterladen der
                    // benötigten Projektdateien aus verschiedenen Quellen.
                    new DownloadProcess(),
                    
                    // Der Contao-Manager-Prozess bildet das Pendant zum
                    // Contao-Manager-Schritt ab und führt die Installation
                    // von Abhängigkeiten sowie die Datenbankmigration durch.
                    new ContaoManagerProcess(),
                    
                    // Der Produkt-Registrierungsprozess übermittelt die
                    // bisher gesammelten Informationen an den Shop, um
                    // dieses Produkt als registriert zu markieren.  
                    new RegisterProductProcess()
                )
        );
    }

    /**
     * @inheritDoc
     */
    function getConfig(): array
    {
        return [
            // Name der Bridge / des Shops für den diese Bridge fungiert
            'name'        => 'ThemeManager',
            
            // Der Anzeigename für die Schnittstellenübersicht innerhalb des Produkt Installers
            'title'       => 'ThemeManager Produkte',
            
            // Die Beschreibung für die Schnittstellenübersicht
            'description' => 'Registrieren Sie Produkte des ThemeManager-Shops.',
            
            // Ein Logo für die Schnittstellenübersicht
            'image'       => 'path/to/image.svg',
            
            // Der Einstiegspunkt der API des Shop-Systems.
            'entry'       => 'https://mein-shop.com/api'
        ];
    }
}
```

Im obigen Codebeispiel wird eine Bridge für den [ThemeManager Shop](https://www.contao-thememanager.com) erstellt. In der Methode `setSteps()` werden die Schritte definiert, die im Produkt Installer ausgeführt werden sollen wenn ein neues Produkt registriert wird. Hier werden verschiedene Schritte wie der Lizenzschritt, der Produktschritt, der Werbeschritt, der Contao Manager-Schritt und der Installationsprozess-Schritt hinzugefügt. Jeder Schritt kann verschiedene Prozesse enthalten, die im Installationsablauf ausgeführt werden.

!> Die Reihenfolge der oben aufgeführten Schritte und Prozesse muss dabei eingehalten werden. Es steht jedoch frei, weitere eigene Prozesse dazwischen zu definieren. 

In der Methode `getConfig()` wird die Konfiguration der Bridge festgelegt. Hier werden Informationen wie der Name des ThemeManagers, der Titel, die Beschreibung, das Bild und der Einstiegspunkt für die API des Shops angegeben.

Dieser Code ist ein Beispiel und kann je nach den spezifischen Anforderungen und Strukturen der Bridge angepasst werden. Er dient als Ausgangspunkt für die Konfiguration und Integration der Bridge in den Produkt Installer.

## Eigene Prozesse
Im Produkt Installer haben Sie die Möglichkeit, Prozesse wiederzuverwenden und eigene Logik auszuführen. Ein Beispiel dafür ist der `ApiProcess`, mit dem Sie eine API-Route aufrufen können, um spezifische Aktionen wie bspw. eine Systemprüfung (Prüfung auf benötigte Abhängigkeiten für mein Produkt) durchzuführen. Hier ist ein Beispiel für die Verwendung des `ApiProcess`:
```php
use Oveleon\ContaoProductInstaller\ApiProcess;

// ...

// Erstellen Sie einen ApiProcess
$apiProcess = new ApiProcess(
    'API Prozess',
    'Dieser Prozess ruft eine API-Route auf und führt spezifische Logik aus.'
);

// Fügen Sie eine API-Route hinzu
$apiProcess->route('/api/custom-route');

// ...

// Fügen Sie den ApiProcess zum Installationsablauf hinzu
$processStep->addProcess($apiProcess);
```
Mit dem `ApiProcess` können Sie eine API-Route definieren und spezifische Logik für den Prozess implementieren. Sie können den ApiProcess direkt zum Installationsablauf hinzufügen, um bestimmte Aktionen auszuführen.

Erstellen Sie einen `ApiProcess`, indem Sie den Titel und die Beschreibung angeben. Verwenden Sie dann die Methode `route()`, um eine API-Route hinzuzufügen, indem Sie den entsprechenden Pfad zu Ihrem Controller angeben.

Schließlich fügen Sie den ApiProcess Ihrem Installationsablauf hinzu, indem Sie die Methode `addProcess()` verwenden.

Der `ApiProcess` ist ein Beispiel für einen wiederverwendbaren Prozess, der Ihnen ermöglicht, spezifische API-Aufrufe und Logik im Produkt Installer durchzuführen. Sie können ähnliche Ansätze verwenden, um weitere wiederverwendbare Prozesse zu erstellen und Ihre eigene Logik nahtlos in den Installationsablauf einzubinden.

Werfen wir also noch einemal einen Blick auf den vollständigen Code ohne Kommentare:

```php
function setSteps(): void
{
    $apiProcess = new ApiProcess(
        'Mein API Prozess',
        'Dieser Prozess ruft eine API-Route auf und führt spezifische Logik aus.'
    );
    
    $apiProcess->route('/api/custom-route');

    $this->addSteps(
        new LicenseStep(),
        new ProductStep(),
        new AdvertisingStep(),
        new ContaoManagerStep(),
        (new ProcessStep())
            ->addProcesses(
                new DownloadProcess(),
                new ContaoManagerProcess(),
                $apiProcess,
                new RegisterProductProcess()
            )
    );
}
```

Nun, wo wir einen neuen Prozess definiert haben, benötigen wir noch unseren Controller, welcher durch diesen Prozess aufgerufen wird.
Die in dieser Route definierte API leitet zur Bridge weiter. Das bedeutet, dass wir einen Controller erstellen müssen, der auf diese Route hört und die entsprechende Logik enthält.
Wenn wir das obige Beispiel fortsetzen, möchten wir in unserer Logik überprüfen, ob eine Erweiterung, die mein Produkt erfordert, installiert ist.

!> Damit wir ein sauberes Beispiel erhalten, ändern wir vorher noch die Route von `/api/custom-route` in `/api/bridge/systemcheck`.

```php
namespace Oveleon\ContaoThemeManagerBridge\Controller\API;

use Composer\InstalledVersions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/api/bridge/systemcheck',
    name:       SystemCheckProcessController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class SystemCheckProcessController
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Check requirements
     */
    public function __invoke(): JsonResponse
    {
        if(InstalledVersions::isInstalled('contao-thememanager/core'))
        {
            return new JsonResponse([
                'status' => 'OK'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'messages' => [
                'Bitte installieren Sie den Contao ThemeManager vor der Einrichtung des Produktes.'
            ]
        ], Response::HTTP_NOT_ACCEPTABLE);
    }
}
```

Jetzt, da wir unsere Logik implementiert haben, wird während der Installation unsere Route aufgerufen und der Installationsprozess nur ausgeführt, wenn unsere Bedingung zutrifft. In diesem Fall prüfen wir auf das vorhanden sein einer anderen Erweiterung und werfen einen Fehler zurück, wenn diese nicht installiert ist.

## Prozess-Repsonse

Der Rückgabewert eines Prozesses kann einen Erfolgs- und einen Fehlerfall beinhalten und muss dabei als `Symfony\Component\HttpFoundation\JsonResponse` übermittelt werden.

```php
# Alles OK!
return new JsonResponse([
    'status' => 'OK'
], Response::HTTP_OK);

# Ein Fehler ist aufgetreten!
return new JsonResponse([
    'error' => true,
    'messages' => [
        'Bitte installieren Sie den Contao ThemeManager vor der Einrichtung des Produktes.'
    ]
], Response::HTTP_NOT_ACCEPTABLE);
```
