<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DocumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
         return $crud
            ->setEntityLabelInSingular('un document')
            ->setEntityLabelInPlural('Liste de documents')
            ->setSearchFields(['title', 'author','lastName', 'firstName', 'role'])
            ->setDateFormat('dd/MM/yyyy')
            ->setDateTimeFormat('dd/MM/yyyy - HH:mm:ss')
            ->setTimeFormat('HH:mm:ss')
            ->setDecimalSeparator(',')
            ->setThousandsSeparator(' ')
            ->setPageTitle('index', 'Liste des documents')
            ->setPageTitle('new', 'Ajouter un nouveau document')
            ->setPageTitle('edit', 'Modifier un document')
            ->setDefaultSort(['id' => 'DESC', 'title' => 'ASC', 'author'=>'ASC','createdAt' => 'DESC'])
            ->setPaginatorPageSize(10);
        }
   /*     
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('ducuments'));
    }
    */
   
    public function configureFields(string $pageName): iterable
    {
        $mappingParameter = $this->getParameter('vich_uploader.mappings');
        $documentMapping = $mappingParameter['document']['uri_prefix'];
       
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Saisir un titre :');
        yield TextField::new('author', 'Saisir un auteur :');
        yield TextareaField::new('description', 'Saisir la description du document :');

        yield ImageField::new('fileNameDocument')
        ->setBasePath($documentMapping)
        ->setLabel('Photo')
        ->hideOnForm();

        yield TextareaField::new('imageNameDocument')
        ->hideOnIndex()
        ->setLabel('Charger un document de type pdf ou epub :')
        ->setFormType(VichImageType::class, 
        [
            'constraints' => [
                new File([
                    'maxSize' => '2000k',
                    'mimeTypes' => [
                        'document/pdf',
                        'document/epub',
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (pdf, epub) !',
                ],)
                ],
        ],
    );
      
        $createdAt = DateTimeField::new('createdAt')
        ->setFormTypeOptions([
            'years' => range(date('Y'), date('Y') + 5),
            'widget' => 'single_text',
        ]);
       // ->setLabel('Date de création');

        $publishAt = DateTimeField::new('publishAt')
        ->setFormTypeOptions([
            'years' => range(date('Y'), date('Y') + 5),
            'widget' => 'single_text',
        ])
        ->setLabel('Date de publication');


        if (Crud::PAGE_EDIT === $pageName) {
                        yield $createdAt ->setFormTypeOption('disabled', true) ;
                        yield $publishAt ;
                    } else {
                        yield $createdAt;
                        yield $publishAt;
                    }  
       
    
     
        $isPublished = BooleanField::new('isPublished','Status ')
        ->setHelp('Veuillez cocher pour publier le document.')
        ->setLabel('Status');

        yield $isPublished;

    } 
   
}