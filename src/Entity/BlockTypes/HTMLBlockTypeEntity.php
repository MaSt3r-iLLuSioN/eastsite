<?php

namespace App\Entity\BlockTypes;
use App\Entity\BlockEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Form\Type\CKEditorType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
/**
 * @ORM\Entity(repositoryClass="App\Repository\HTMLBlockTypeEntityRepository")
 */
class HTMLBlockTypeEntity implements BlockTypeEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
     private $content;
     
     /**
     * One HTML Block has(is) one Block.
     * @ORM\OneToOne(targetEntity="App\Entity\BlockEntity")
     */
     private $block;
     
     private $em;
     private $container;
     public function setContainer(ContainerInterface $container) {
         $this->container = $container;
     }
     public function getId() : int
     {
         return $this->id;
     }
     public function getContent()
     {
         return $this->content;
     }
     public function setContent($content)
     {
         if($content == null)
             $this->content = 'Block Content';
         else
         {
            $content = str_replace('<blockquote>', '<blockquote class="blockquote">', $content);
            $this->content = $content;
         }
     }
     public function getBlock() : BlockEntity
     {
         return $this->block;
     }
     public function setBlock(BlockEntity $block)
     {
         $this->block = $block;
     }
     
     public function setForm(FormBuilderInterface $form, ContainerInterface $container): Form
     {
         return $form->add('content', CKEditorType::class, array(
             'twig'=>$container->get('twig'),
             'dispatcher'=>$container->get('event_dispatcher'),
             'attr' => array(
                 'class'=>'testCKeditor'
             ),
             'data'=>$this->getContent(),
             'id'=>'form_content'
         ))->getForm();
     }
     
     public function saveData(EntityManager $em, Form $form, BlockEntity $block, Request $request, bool $first = false): BlockTypeEntityInterface
     {
         $this->setBlock($block);
         if($first == true)
         {
            $this->setContent(''); 
         }
         else
         {
            $this->setContent($form->get('content')->getData());
         }
         $em->persist($this);
         $em->flush();
         return $this;
     }
     
    public function removeData(EntityManager $em)
    {
        
    }
     
     public function getTypeClass(): string
     {
         return 'HTML';
     }
     
     public function getParams(): array
     {
         $params = array();
         $params['content'] = $this->getContent();
         return $params;
     }

    public function __construct(EntityManagerInterface $em, ContainerInterface $container) {
        $this->em = $em;
        $this->container = $container;
    }

}
