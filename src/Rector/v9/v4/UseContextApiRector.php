<?php

declare(strict_types=1);

namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85389-VariousPublicPropertiesInFavorOfContextAPI.html
 */
final class UseContextApiRector extends AbstractRector
{
    /**
     * @var array
     */
    private const REFACTOR_PROPERTIES = [
        'loginUser',
        'gr_list',
        'beUserLogin',
        'showHiddenPage',
        'showHiddenRecords',
    ];

    /**
     * @var Typo3NodeResolver
     */
    private $typo3NodeResolver;

    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }

    public function getNodeTypes(): array
    {
        return [PropertyFetch::class];
    }

    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->var, TypoScriptFrontendController::class)
             && ! $this->typo3NodeResolver->isPropertyFetchOnAnyPropertyOfGlobals(
                $node,
                Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER
            )) {
            return null;
        }

        if (! $this->isNames($node->name, self::REFACTOR_PROPERTIES)) {
            return null;
        }

        $propertyName = $this->getName($node->name);

        $staticCall = $this->createStaticCall(GeneralUtility::class, 'makeInstance', [
            $this->createClassConstantReference(Context::class),
        ]);

        $contextCall = $this->createMethodCall($staticCall, 'getPropertyFromAspect');

        if ('loginUser' === $propertyName) {
            $contextCall->args = $this->createArgs(['frontend.user', 'isLoggedIn']);

            return $contextCall;
        }

        if ('gr_list' === $propertyName) {
            $contextCall->args = $this->createArgs(['frontend.user', 'groupIds']);

            return $this->createFuncCall('implode', [new String_(','), $contextCall]);
        }

        if ('beUserLogin' === $propertyName) {
            $contextCall->args = $this->createArgs(['backend.user', 'isLoggedIn']);

            return $contextCall;
        }

        if ('showHiddenPage' === $propertyName) {
            $contextCall->args = $this->createArgs(['visibility', 'includeHiddenPages']);

            return $contextCall;
        }

        if ('showHiddenRecords' === $propertyName) {
            $contextCall->args = $this->createArgs(['visibility', 'includeHiddenContent']);

            return $contextCall;
        }

        return null;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Various public properties in favor of Context API',
            [
                new CodeSample(
                    <<<'PHP'
$frontendUserIsLoggedIn = $GLOBALS['TSFE']->loginUser;
$groupList = $GLOBALS['TSFE']->gr_list;
$backendUserIsLoggedIn = $GLOBALS['TSFE']->beUserLogin;
$showHiddenPage = $GLOBALS['TSFE']->showHiddenPage;
$showHiddenRecords = $GLOBALS['TSFE']->showHiddenRecords;
PHP
                    ,
                    <<<'PHP'
$frontendUserIsLoggedIn = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getPropertyFromAspect('frontend.user', 'isLoggedIn');
$groupList = implode(',', \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getPropertyFromAspect('frontend.user', 'groupIds'));
$backendUserIsLoggedIn = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getPropertyFromAspect('backend.user', 'isLoggedIn');
$showHiddenPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getPropertyFromAspect('visibility', 'includeHiddenPages');
$showHiddenRecords = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getPropertyFromAspect('visibility', 'includeHiddenContent');
PHP
                ),
            ]
        );
    }
}