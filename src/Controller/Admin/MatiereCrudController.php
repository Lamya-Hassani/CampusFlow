<?php

namespace App\Controller\Admin;

use App\Entity\Matiere;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MatiereCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Matiere::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // ID only visible on index/detail
            IdField::new('id')
                ->onlyOnIndex(),

            TextField::new('libelle', 'Libellé de la matière')
                ->setRequired(true),

            IntegerField::new('coefficient', 'Coefficient')
                ->setRequired(true),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Matières')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une matière')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la matière')
            ->setSearchFields(['libelle'])
            ->setDefaultSort(['libelle' => 'ASC']);
    }
}
