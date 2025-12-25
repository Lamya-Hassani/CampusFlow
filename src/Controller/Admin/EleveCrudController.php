<?php

namespace App\Controller\Admin;

use App\Entity\Eleve;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EleveCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Eleve::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addPanel('Informations personnelles'),

            TextField::new('nom', 'Nom'),
            TextField::new('prenom', 'Prénom'),
            DateField::new('dateNais', 'Date de naissance'),
            TextField::new('adresse', 'Adresse'),
            TextField::new('nomParent', 'Nom du parent'),
            TextField::new('telParent', 'Téléphone du parent'),

            FormField::addPanel('Classe'),
            AssociationField::new('classe')
                ->setLabel('Classe')
                ->autocomplete(),

            FormField::addPanel('Compte utilisateur'),
            AssociationField::new('user')
                ->setLabel('Utilisateur')
                ->autocomplete(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des Élèves')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un élève')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un élève')
            ->setSearchFields(['nom', 'prenom', 'nomParent'])
            ->setDefaultSort(['nom' => 'ASC', 'prenom' => 'ASC']);
    }
}
