# Konfiguration

Die Konfiguration einer Bridge ist entscheidend, um die gewünschten Schritte und Prozesse im Produkt-Installer zu definieren. Hier ist ein Beispielcode, der zeigt, wie die Konfiguration einer Bridge aussehen kann:

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
        // Hinzufügen von Schritten
        $this->addSteps(
            // Lizenzschritt hinzufügen
            new LicenseStep(),

            // Produktschritt hinzufügen
            new ProductStep(),

            // Werbeschritt hinzufügen
            new AdvertisingStep(),

            // Contao Manager-Schritt hinzufügen
            new ContaoManagerStep(),

            // Installationsprozess-Schritt hinzufügen
            (new ProcessStep())
                ->addProcesses(
                    new DownloadProcess(),
                    new ContaoManagerProcess(),
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
            'name'          => 'ThemeManager',
            'title'         => 'ThemeManager Produkte',
            'description'   => 'Registrieren Sie Produkte des ThemeManager-Shops.',
            'image'         => 'path/to/image.svg',
            'entry'         => 'http://theme-manager-shop.com/api'
        ];
    }
}
```

Im obigen Codebeispiel wird eine Bridge für den ThemeManager erstellt. In der Methode `setSteps()` werden die Schritte definiert, die im Produkt-Installer ausgeführt werden sollen. Hier werden verschiedene Schritte wie der Lizenzschritt, der Produktschritt, der Werbeschritt, der Contao Manager-Schritt und der Installationsprozess-Schritt hinzugefügt. Jeder Schritt kann verschiedene Prozesse enthalten, die im Installationsablauf ausgeführt werden.

!> Die Reihenfolge der oben aufgeführten Schritte und Prozesse muss dabei eingehalten werden. Es steht jedoch frei, weitere eigene Prozesse dazwischen zu definieren. 

In der Methode `getConfig()` wird die Konfiguration der Bridge festgelegt. Hier werden Informationen wie der Name des ThemeManagers, der Titel, die Beschreibung, das Bild und der Einstiegspunkt für die API des Shops angegeben.

Dieser Code ist ein Beispiel und kann je nach den spezifischen Anforderungen und Strukturen der Bridge angepasst werden. Er dient als Ausgangspunkt für die Konfiguration und Integration der Bridge in den Produkt-Installer.

## Eigene Prozesse
Im Produkt-Installer haben Sie die Möglichkeit, Prozesse wiederzuverwenden und eigene Logik auszuführen. Ein Beispiel dafür ist der `ApiProcess`, mit dem Sie eine API-Route aufrufen können, um spezifische Aktionen wie bspw. eine Systemprüfung (Prüfung auf benötigte Abhängigkeiten für mein Produkt) durchzuführen. Hier ist ein Beispiel für die Verwendung des `ApiProcess`:
```php
use Oveleon\ContaoProductInstaller\ApiProcess;

// ...

// Erstellen Sie einen ApiProcess
$apiProcess = new ApiProcess(
    'API Prozess',
    'Dieser Prozess ruft eine API-Route auf und führt spezifische Logik aus.'
);

// Fügen Sie eine API-Route hinzu
$apiProcess->addRoute(ApiProcess::ROUTE, '/api/custom-route');

// Fügen Sie den ApiProcess zum Installationsablauf hinzu
$processStep->addProcess($apiProcess);
```
Mit dem `ApiProcess` können Sie eine API-Route definieren und spezifische Logik für den Prozess implementieren. Sie können den ApiProcess direkt zum Installationsablauf hinzufügen, um bestimmte Aktionen auszuführen.

Erstellen Sie einen `ApiProcess`, indem Sie den Titel und die Beschreibung angeben. Verwenden Sie dann die Methode `addRoute()`, um eine API-Route hinzuzufügen, indem Sie den Namen der Route und den entsprechenden Pfad angeben.

Schließlich fügen Sie den ApiProcess Ihrem Installationsablauf hinzu, indem Sie die Methode `addProcess()` verwenden.

Der `ApiProcess` ist ein Beispiel für einen wiederverwendbaren Prozess, der Ihnen ermöglicht, spezifische API-Aufrufe und Logik im Produkt-Installer durchzuführen. Sie können ähnliche Ansätze verwenden, um weitere wiederverwendbare Prozesse zu erstellen und Ihre eigene Logik nahtlos in den Installationsablauf einzubinden.

Im Gesamten könnte das nun so aussehen:

```php
function setSteps(): void
{
    // Erstellen Sie einen ApiProcess
    $apiProcess = new ApiProcess(
        'API Prozess',
        'Dieser Prozess ruft eine API-Route auf und führt spezifische Logik aus.'
    );
    
    // Fügen Sie eine API-Route hinzu
    $apiProcess->addRoute(ApiProcess::ROUTE, '/api/custom-route');

    // Hinzufügen von Schritten
    $this->addSteps(
        // Lizenzschritt hinzufügen
        new LicenseStep(),

        // Produktschritt hinzufügen
        new ProductStep(),

        // Werbeschritt hinzufügen
        new AdvertisingStep(),

        // Contao Manager-Schritt hinzufügen
        new ContaoManagerStep(),

        // Installationsprozess-Schritt hinzufügen
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

Die in dieser Route definierte API leitet zur Bridge weiter. Das bedeutet, dass wir einen Controller erstellen müssen, der auf diese Route hört und die entsprechende Logik enthält.
Wenn wir das obige Beispiel fortsetzen, möchten wir in unserer Logik überprüfen, ob eine Erweiterung, die mein Produkt erfordert, installiert ist.

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

Jetzt, da wir unsere Logik implementiert haben, werfen wir einen erneuten Blick auf den vollständigen Code:

```php
use Oveleon\ContaoThemeManagerBridge\Controller\API\SystemCheckProcessController;

...

function setSteps(): void
{
    $router = Controller::getContainer()->get('router');
    $translator = Controller::getContainer()->get('translator');

    // Create processes
    $systemCheckProcess = (
        new ApiProcess(
            'Systemprüfung',
            'Abhängigkeiten des Produktes werden geprüft'
        )
    )->addRoute(
        ApiProcess::ROUTE, 
        $router->generate(SystemCheckProcessController::class)
    );

    // Create steps
    $this->addSteps(
        // Add license step
        new LicenseStep(),

        // Add product preview step
        new ProductStep(),

        // Add advertising step
        new AdvertisingStep(),

        // Add contao manager step
        new ContaoManagerStep(),

        // Add install process step
        (new ProcessStep())
            ->addProcesses(
                new DownloadProcess(),
                new ContaoManagerProcess(),
                $systemCheckProcess,
                new RegisterProductProcess()
            )
    );
}
```
