<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity\BlockTypes;
use App\Entity\BlockEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
/**
 * Description of BlockTypeEntityInterface
 *
 * @author Trey
 */
interface BlockTypeEntityInterface {
    //put your code here
    public function __construct(EntityManagerInterface $em, ContainerInterface $container);
    public function getId() : int;
    public function getBlock() : BlockEntity;
    public function setBlock(BlockEntity $block);
    public function setForm(FormBuilderInterface $form, ContainerInterface $container) : Form;
    public function saveData(EntityManager $em, Form $form, BlockEntity $block, Request $request, bool $first = false) : BlockTypeEntityInterface;
    public function getTypeClass() : string;
    public function getParams() : array;
    public function setContainer(ContainerInterface $container);
    public function removeData(EntityManager $em);
}
