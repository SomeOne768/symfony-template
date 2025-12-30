<?php

declare(strict_types=1);

use App\Common\Doctrine\ORM\TagAwareResultCache\TagAwareEntityRepository;
use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\DependsOnlyOnTheseNamespaces;
use Arkitect\Expression\ForClasses\Extend;
use Arkitect\Expression\ForClasses\HaveAttribute;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\IsEnum;
use Arkitect\Expression\ForClasses\IsFinal;
use Arkitect\Expression\ForClasses\IsNotAbstract;
use Arkitect\Expression\ForClasses\IsNotEnum;
use Arkitect\Expression\ForClasses\IsNotInterface;
use Arkitect\Expression\ForClasses\IsNotTrait;
use Arkitect\Expression\ForClasses\MatchOneOfTheseNames;
use Arkitect\Expression\ForClasses\NotDependsOnTheseNamespaces;
use Arkitect\Expression\ForClasses\NotResideInTheseNamespaces;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(__DIR__ . '/src');

    $unitRules = [];

    // NAMING

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\*\Action'))
        // some classes contain 'Actions' word, to refer to deposit actions
        ->andThat(new NotResideInTheseNamespaces(
                'App\Deposit\Actions\Collection',
                'App\Deposit\Actions\Config',
                'App\Deposit\Actions\Contracts',
                'App\Deposit\Actions\ValueObject',
                'App\Deposit\Actions\ModalPresenter',
                'App\Deposit\Actions\Factory',
                'App\Deposit\Actions\Dto',
                'App\Deposit\Actions\Service',
                'App\Deposit\Actions\Component',
                'App\Application\DependencyInjection',
                'App\Common\AppEnum',
                'App\User\Administer\Component\Actions',
            )
        )
        ->andThat(new IsNotTrait())
        ->should(new HaveNameMatching('*Action'))
        ->because('Actions classes must have name ending with "Action"')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\*\Action'))
        // some classes contain 'Actions' word, to refer to deposit actions
        ->andThat(new NotResideInTheseNamespaces(
                'App\Deposit\Actions\Collection',
                'App\Deposit\Actions\Config',
                'App\Deposit\Actions\Contracts',
                'App\Deposit\Actions\ValueObject',
                'App\Deposit\Actions\ModalPresenter',
                'App\Deposit\Actions\Factory',
                'App\Deposit\Actions\Dto',
                'App\Deposit\Actions\Service',
                'App\User\Administer\Component\Actions',
            )
        )
        ->andThat(new IsNotAbstract())
        ->andThat(new IsNotTrait())
        ->should(new HaveAttribute(AsController::class))
        ->because('it\'s a controller')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\*\Dto\*'))
        ->andThat(new IsNotTrait())
        ->andThat(new IsNotInterface())
        ->andThat(new IsNotEnum())
        ->should(new MatchOneOfTheseNames(['*Dto', '*DtoList']))
        ->because('Dto must be identified with name ending with "Dto" or "DtoList')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\*\Enum\*'))
        ->andThat(new IsEnum())
        ->should(new HaveNameMatching('*Enum'))
        ->because('AppEnum classes must have name ending with "Enum"')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\*\Repository\*'))
        ->andThat(new IsNotTrait())
        ->andThat(new IsNotInterface())
        ->andThat(new IsNotEnum())
        ->should(new HaveNameMatching('*Repository'))
        ->because('Repositories must have a name ending with "Repository"')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new HaveNameMatching('*Action'))
        // some classes contain 'Actions' word, to refer to deposit actions
        ->andThat(new NotResideInTheseNamespaces(
                'App\Deposit\Actions\Collection',
                'App\Deposit\Actions\Config',
                'App\Deposit\Actions\Contracts',
                'App\Deposit\Actions\ValueObject',
                'App\Deposit\Actions\ModalPresenter',
                'App\Deposit\Actions\Factory',
                'App\Deposit\Actions\Dto',
                'App\Deposit\Actions\Service',
                'App\Application\DependencyInjection',
                'App\Common\AppEnum')
        )
        ->should(new ResideInOneOfTheseNamespaces('App\*\Action'))
        ->because('Action (controllers) must be in a specific namespace')
    ;

    /* VOTERS */

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\*\Voter'))
        ->should(new HaveNameMatching('*Voter'))
        ->because('Voter classes must have a name ending with "Voter"')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\*\Voter'))
        ->should(new Extend(Voter::class))
        ->because(sprintf('Custom Voters must extend %s', Voter::class))
    ;


    /* COMMON - Doctrine Type */

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Common\Doctrine\DBAL\Types'))
        ->should(new HaveNameMatching('*Type'))
        ->because('This is Doctrine DBAL Type')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Common\Doctrine\DBAL\Types'))
        ->should(new Extend(Type::class))
        ->because(sprintf('Custom Doctrine Types must extends %s', Type::class))
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Common\Doctrine\DBAL\Types'))
        ->should(new DependsOnlyOnTheseNamespaces(['Doctrine\DBAL']))
        ->because('Custom Doctrine types must not have any dependencies')
    ;


    /* COMMON - ENTITY */
    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Common\Entity'))
        ->should(new HaveAttribute(Entity::class))
        ->because('All entities must have this Doctrine Entity attribute')
    ;

    // COMMON - REPOSITORY

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Common\Repository\Doctrine'))
        ->andThat(new IsNotInterface())
        ->should(new Extend(ServiceEntityRepository::class, TagAwareEntityRepository::class))
        ->because('All repositories must extend ServiceEntityRepository')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Common\Repository\Doctrine'))
        ->andThat(new IsNotInterface())
        ->andThat(new IsNotEnum())
        ->andThat(new IsNotTrait())
        ->should(new IsFinal())
        ->because('Repositories must not be extended')
    ;

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Common\Repository\Doctrine'))
        ->should(new NotDependsOnTheseNamespaces(['App\*\Action']))
        ->because('Repositories must not depends on Actions')
    ;

    // COMMON - ENUM

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Common\AppEnum'))
        ->should(new IsEnum())
        ->because('App\Common\AppEnum directory must contains only Enums')
    ;


    // SEARCH

    $unitRules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Deposit\Search'))
        ->should(new NotDependsOnTheseNamespaces(['App\Deposit\View']))
        ->because('Search must not depend on View')
    ;


    $config->add($classSet, ...$unitRules);
};
