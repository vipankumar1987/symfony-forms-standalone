<?php

if(isset($_GET['info']) && $_GET['info']==1){
phpinfo();
die;
}
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set('display_errors', 1);

require_once __DIR__ . '/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

// register classes with namespaces
$loader->registerNamespaces(array(
    'Symfony\Component' => __DIR__ . '/',
    'Symfony\Bundle' => __DIR__ . '/',
));

// register a library using the PEAR naming convention
// activate the autoloader
$loader->register();

use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;

use Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\TranslatorHelper;

use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormRendererEngineInterface;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderAdapter;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
// create a Session object from the HttpFoundation component
$session = new Session();

$csrfGenerator = new UriSafeTokenGenerator();
$csrfStorage = new SessionTokenStorage($session);
$csrfManager = new CsrfTokenManager($csrfGenerator, $csrfStorage);

$translator = new Translator('en');
// somehow load some translations into it
$translator->addLoader('xlf', new XliffFileLoader());
$translator->addResource(
    'xlf',
    __DIR__.'/path/to/translations/messages.en.xlf',
    'en'
);

$validator = Validation::createValidator();

$vendorDir = realpath(__DIR__.'/../vendor');
$vendorFormDir = $vendorDir.'/symfony/form';
$vendorValidatorDir = $vendorDir.'/symfony/validator';

// create the validator - details will vary
$validator = Validation::createValidator();

// there are built-in translations for the core error messages
$translator->addResource(
    'xlf',
    $vendorFormDir.'/Resources/translations/validators.en.xlf',
    'en',
    'validators'
);
$translator->addResource(
    'xlf',
    $vendorValidatorDir.'/Resources/translations/validators.en.xlf',
    'en',
    'validators'
);

$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfExtension($csrfManager))
    ->addExtension(new ValidatorExtension($validator))
    ->getFormFactory();

$form = $formFactory->createBuilder()
    ->add('task', TextType::class)
    ->add('dueDate', TextType::class)
    ->getForm();


/**
 * Template name parser
 * @see Symfony\Bundle\FrameworkBundle\Tests\Templating\Helper\Fixtures\StubTemplateNameParser
 *
 * Needed to load the templates used for rendering form items.
 */
class StubTemplateNameParser implements TemplateNameParserInterface
{
    private $root;
    private $rootTheme;
    public function __construct($root, $rootTheme)
    {
        $this->root = $root;
        $this->rootTheme = $rootTheme;
    }
    public function parse($name)
    {
      list($bundle, $controller, $template) = explode(':', $name);
      if ($template[0] == '_') {
          $path = $this->rootTheme.'/Custom/'.$template;
      } elseif ($bundle === 'TestBundle') {
          $path = $this->rootTheme.'/'.$controller.'/'.$template;
      } else {
          $path = $this->root.'/'.$controller.'/'.$template;
      }
      return new TemplateReference($path, 'php');
    }
}

$root = realpath(__DIR__ . '/Symfony/Bundle/FrameworkBundle/Resources/views');
$rootTheme = realpath(__DIR__ . '/Symfony/Bundle/FrameworkBundle/Resources');
$templateNameParser = new StubTemplateNameParser($root, $rootTheme);
$loader = new FilesystemLoader(array());

$defaultThemes = array();
$csrfTokenManager = $csrfManager;//new CsrfProviderAdapter($csrfTokenManager);
$engine = new PhpEngine($templateNameParser, $loader);
$form_helper = new FormHelper(new FormRenderer(new TemplatingRendererEngine($engine, $defaultThemes), $csrfTokenManager));

/**
 * This helper will help rendering form items
 */
/*$form_helper = new FormHelper($engine, array(
    'FrameworkBundle:Form',
));
*/
$engine->setHelpers(array(
    $form_helper
));

$form_view = $form->createView();
?>


<!DOCTYPE html>
<html>
    <body>
        <form action="" method="post"
            <?php print $form_helper->enctype($form_view) ?>
            novalidate="novalidate">
            <?php print $form_helper->widget($form_view) ?></div>
            <input type="submit" />
        </form>

        <?php if ($submitted && $valid) : ?>
        <p><strong>Submitted form is valid.</strong></p>
        <?php endif; ?>

        <p><em>Message object:</em></p>
        <pre><?php print print_r($message, true); ?></pre>
    </body>
</html>