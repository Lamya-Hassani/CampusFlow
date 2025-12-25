<?php

namespace App\Controller\Admin;

use App\Entity\Classe;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClasseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Classe::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom')
                ->setLabel('Nom de la classe')
                ->setSortable(true),
            TextField::new('niveau')
                ->setLabel('Niveau')
                ->setSortable(true),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Gestion des Classes')
            ->setPageTitle('new', 'Créer une nouvelle classe')
            ->setPageTitle('edit', 'Modifier la classe')
            ->setPageTitle('detail', 'Détails de la classe')
            ->setSearchFields(['nom', 'niveau'])
            ->setDefaultSort(['nom' => 'ASC']);
    }
}