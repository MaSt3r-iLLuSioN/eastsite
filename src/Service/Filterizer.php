<?php
namespace App\Service;
use Twig_Environment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
/**
 * Description of Filterizer
 *
 * @author Trey
 */
class Filterizer {
    private $twig;
    private $eventDispatcher;
    private $em;
    private $repo;
    private $data;
    
    //liker properties
    private $useLiker = false;
    private $liker;
        
    //database schemes
    private $dbWhere;
    private $dbOrder;
    private $dbLimit;
    private $dbOffset;
    
    //form variable
    private $perPage;
    private $sortBy;
    private $searchFields;
    private $nodeView;
    
    //pagination variables
    private $numberOfNodes;
    private $currentPage;
    private $pageCount;
    private $paginationUrl;
    
    //template variables
    private $listTemplate;
    private $gridTemplate;
    private $tableStartTemplate;
    private $tableTemplate;
    private $tableEndTemplate;
    private $activeTemplate;
    
    private $templateType;
    
    public function __construct(Twig_Environment $twig, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $em) {
        $this->twig = $twig;
        $this->eventDispatcher = $eventDispatcher;
        $this->em = $em;
        
        $this->data = array();
        
        $this->dbWhere = array();
        $this->dbOrder = array();
        
        $this->perPage = 0;
        $this->sortBy = 0;
        $this->searchFields = array();
        $this->nodeView = 0;
        
        $this->numberOfNodes = 0;
        $this->currentPage = 0;
        $this->pageCount = 0;
    }
    
    public function setUseLiker(bool $use)
    {
        $this->useLiker = $use;
    }
    
    public function setLiker(Liker $liker)
    {
        $this->liker = $liker;
    }
    
    public function addSearchField($fieldName, $fieldPlaceholder,$dbName)
    {
        $this->searchFields[] = array(
            'name'=>$fieldName,
            'placeholder'=>$fieldPlaceholder,
            'value'=>'',
            'dbName'=>$dbName
        );
    }
    
    public function addWhere($name,$value,$type)
    {
        $this->dbWhere[] = array(
            'name'=>$name,
            'type'=>$type,
            'value'=>$value
        );
    }
    
    public function addOrder($name, $value)
    {
        $this->dbOrder[$name] = $value;
    }
    
    public function setPaginationUrl($url)
    {
        $this->paginationUrl = $url;
    }
    public function setListTemplate($listTemplate)
    {
        $this->listTemplate = $listTemplate;
    }
    
    public function setGridTemplate($gridTemplate)
    {
        $this->gridTemplate = $gridTemplate;
    }
    public function setTableStartTemplate($tableStartTemplate)
    {
        $this->tableStartTemplate = $tableStartTemplate;
    }
    public function setTableTemplate($tableTemplate)
    {
        $this->tableTemplate = $tableTemplate;
    }
    public function setTableEndTemplate($tableEndTemplate)
    {
        $this->tableEndTemplate = $tableEndTemplate;
    }
    
    public function setRepo($repo)
    {
        $this->repo = $this->em->getRepository($repo);
    }
    
    private function getNodes()
    {
        $qb = $this->repo->createQueryBuilder('o');
        $qb->select('o');
        //where clauses
        foreach($this->dbWhere as $key=>$where)
        {
            $whereName = $where['name'];
            $whereValue = $where['value'];
            $whereType = $where['type'];
            //primary where
            if($key = 0)
                $qb->where("o.$whereName $whereType :$whereName");
            else
                $qb->andWhere("o.$whereName $whereType :$whereName");
        }
        //orderby clauses
        foreach($this->dbOrder as $key=>$order)
        {
            $qb->orderBy("o.$key",$order);
        }
        
        //set parameters
        foreach($this->dbWhere as $where)
        {
            $whereName = $where['name'];
            $whereValue = $where['value'];
            $whereType = $where['type'];
            if($whereType == 'LIKE')
                $qb->setParameter($whereName,$whereValue.'%');
            else
                $qb->setParameter($whereName,$whereValue);
        }
        $qb->setFirstResult($this->dbOffset);
        $qb->setMaxResults($this->dbLimit);
        
        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    private function countNodes()
    {
        $qb =  $this->repo->createQueryBuilder('o');
        $qb->select('count(o.id)');
        //where clauses
        foreach($this->dbWhere as $key=>$where)
        {
            $whereName = $where['name'];
            $whereValue = $where['value'];
            $whereType = $where['type'];
            //primary where
            if($key = 0)
                $qb->where("o.$whereName $whereType :$whereName");
            else
                $qb->andWhere("o.$whereName $whereType :$whereName");
        }
        //set parameters
        foreach($this->dbWhere as $where)
        {
            $whereName = $where['name'];
            $whereValue = $where['value'];
            $whereType = $where['type'];
            if($whereType == 'LIKE')
                $qb->setParameter($whereName,$whereValue.'%');
            else
                $qb->setParameter($whereName,$whereValue);
        }
        $query = $qb->getQuery();
        return $query->getSingleScalarResult();
    }
    
    public function setContent()
    {
        //count total number of nodes
        $this->numberOfNodes = $this->countNodes();
        //setup pagination
        $this->data['pagination']['pageCount'] = (int) ceil($this->numberOfNodes / $this->dbLimit);
        if($this->currentPage > $this->data['pagination']['pageCount']) 
        {
            $this->currentPage = 1;
        }
        $this->data['pagination']['currentPage'] = $this->currentPage;
        $this->data['pagination']['url'] = $this->paginationUrl;
        $this->data['pagination']['previousPage'] = $this->currentPage - 1;
        $this->data['pagination']['nextPage'] = $this->currentPage + 1;
        $this->data['pagination']['perPage'] = $this->perPage;
        $this->data['pagination']['sortBy'] = $this->sortBy;
        $this->data['pagination']['searchFields'] = $this->searchFields;
        $this->data['pagination']['nodeView'] = $this->nodeView;
        $this->data['pagination']['numberOfNodes'] = $this->numberOfNodes;
        $this->data['pagination']['searchFields'] = $this->searchFields;
        $startDisplay = 1;
        $endDisplay = $this->dbLimit;
        if($endDisplay > $this->numberOfNodes)
                $endDisplay = $this->numberOfNodes;
        if($this->currentPage > 1)
        {
            $startDisplay = ($this->dbLimit * ($this->currentPage - 1))+1;
            $endDisplay = ($startDisplay + $this->dbLimit) - 1;
            if($endDisplay > $this->numberOfNodes)
                $endDisplay = $this->numberOfNodes;
        }
        $this->data['pagination']['startDisplay'] = $startDisplay;
        $this->data['pagination']['endDisplay'] = $endDisplay;
                
        $this->dbOffset = ($this->currentPage - 1) * $this->dbLimit;
        
        $this->data['tableStartTemplate'] = $this->tableStartTemplate;
        $this->data['tableEndTemplate'] = $this->tableEndTemplate;
        $this->data['activeTemplate'] = $this->activeTemplate;
        $this->data['nodes'] = $this->getNodes();
        if($this->useLiker == true)
        {
            foreach($this->data['nodes'] as $node)
            {
                $this->liker->setNode($node);
                $this->data['likes'][$node->getId()] = $this->liker->createView(); 
            }
            $this->data['useLiker'] = true;
        }
        
        $this->data['numberOfNodes'] = $this->numberOfNodes;
    }
    
    public function initFilterizer()
    {
        $javascriptContent = $this->twig->render('libraries/filterizer/main.js.html.twig', array(
            'searchFields'=>$this->searchFields
        ));
        $cssContent = $this->twig->render('libraries/filterizer/main.css.html.twig',array());
        $this->eventDispatcher->addListener('kernel.response', function($event) use ($javascriptContent, $cssContent) {
            $response = $event->getResponse();
            $content = $response->getContent();
            // finding position of </body> tag to add content before the end of the tag
            $pos = strripos($content, '</body>');
            $content = substr($content, 0, $pos).$javascriptContent.substr($content, $pos);

            $pos = strripos($content, '</head>');
            $content = substr($content, 0, $pos).$cssContent.substr($content, $pos);
            
            $response->setContent($content);
            $event->setResponse($response);
        });
    }
    
    public function handleRequest(Request $request)
    {
        if(isset($_GET['perPage']) && is_numeric($_GET['perPage']) && $_GET['perPage'] <= 2 && $_GET['perPage'] >= 0)
            $this->perPage = $_GET['perPage'];
        if(isset($_GET['sortBy']) && is_numeric($_GET['sortBy']) && $_GET['sortBy'] <= 2 && $_GET['sortBy'] >= 0)
            $this->sortBy = $_GET['sortBy'];
        $searchFieldCount = count($this->searchFields);
        for((int)$i = 0; $i < (int)$searchFieldCount; $i++)
        {
            if(isset($_GET[$this->searchFields[$i]['name']]) && $_GET[$this->searchFields[$i]['name']] != null && $_GET[$this->searchFields[$i]['name']] != '')
            {
                $this->searchFields[$i]['value'] = $_GET[$this->searchFields[$i]['name']];
                $this->addWhere($this->searchFields[$i]['dbName'], $this->searchFields[$i]['value'], 'LIKE');
            }
        }
        if(isset($_GET['nodeView']) && is_numeric($_GET['nodeView']) && $_GET['nodeView'] <= 2 && $_GET['nodeView'] >= 0)
            $this->nodeView = $_GET['nodeView'];
        if(!empty($_GET['currentPage'])) 
        {
            $this->currentPage = filter_input(INPUT_GET, 'currentPage', FILTER_VALIDATE_INT);
            if(false === $this->currentPage)
                $this->currentPage = 1;
        }
        else
            $this->currentPage = 1;
        //set dblimit from perpage
        if($this->perPage == 0)
        {
            $this->dbLimit = 6;
        }
        elseif($this->perPage == 1)
        {
            $this->dbLimit = 12;
        }
        elseif($this->perPage == 2)
        {
            $this->dbLimit = 18;
        }
        //set dborder from sortby
        if($this->sortBy == 1)
            $this->dbOrder['title'] = 'ASC';
        elseif($this->sortBy == 2)
            $this->dbOrder['title'] = 'DESC';
        //set active template
        if($this->nodeView == 0)
        {
            $this->activeTemplate = $this->listTemplate;
            $this->templateType = 'list';
        }
        elseif($this->nodeView == 1)
        {
            $this->activeTemplate = $this->gridTemplate;
            $this->templateType = 'grid';
        }
        else
        {
            $this->activeTemplate = $this->tableTemplate;
            $this->templateType = 'table';
        }
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function createView()
    {
        $this->initFilterizer();
        $filterForm = $this->twig->render('libraries/filterizer/filterForm.html.twig', array(
            'data'=>$this->data,
            'perPage'=>$this->perPage,
            'sortBy'=>$this->sortBy,
            'searchFields'=>$this->searchFields,
            'nodeView'=>$this->nodeView,
            'templateType'=>$this->templateType,
        ));
        return $filterForm;
    }
}
