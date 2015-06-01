<?php
class Bootstrap_Hero_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {    
		$this->loadLayout();
    	$this->renderLayout();
    }
	public function ListAction ()
	{
     	echo 'test list method';
    	$params = $this->getRequest()->getParams();
    	$hero = Mage::getModel('hero/hero');
    	echo("Loading the hero with an ID of ".$params['id']);
    	$hero->load($params['id']);
    	$data = $hero->getData();
    	var_dump($data);
    }
	public function createNewHeroAction() {
    	$hero = Mage::getModel('hero/hero');
    	$hero->setTitle('Another Link');
    	$hero->setDescription('This post was created from code!');
    	$hero->setType('url');
    	$hero->setDetail('http://google.com');
    	$hero->save();
    	echo 'post with ID ' . $hero->getId() . ' created';
	}
	
	/* not needed
public function editFirstPostAction() {
    $blogpost = Mage::getModel('weblog/blogpost');
    $blogpost->load(1);
    $blogpost->setTitle("The First post!");
    $blogpost->save();
    echo 'post edited';
}
public function deleteFirstPostAction() {
    $blogpost = Mage::getModel('weblog/blogpost');
    $blogpost->load(1);
    $blogpost->delete();
    echo 'post removed';
}

public function showAllHeroAction() {
    $posts = Mage::getModel('hero/hero')->getCollection();
    foreach($posts as $hero){
        echo '<h3>'.$hero->getTitle().'</h3>';
        echo nl2br($hero->getDescription());
    }
}
*/

}
