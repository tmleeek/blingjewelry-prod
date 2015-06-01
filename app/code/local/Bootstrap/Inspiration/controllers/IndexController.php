<?php
class Bootstrap_Inspiration_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {    
		$this->loadLayout();
    	$this->renderLayout();
    }
	public function ListAction ()
	{
     	echo 'test list method';
    	$params = $this->getRequest()->getParams();
    	$inspiration = Mage::getModel('inspiration/inspiration');
    	echo("Loading the inspiration with an ID of ".$params['id']);
    	$inspiration->load($params['id']);
    	$data = $inspiration->getData();
    	var_dump($data);
    }
	public function createNewInspirationAction() {
    	$inspiration = Mage::getModel('inspiration/inspiration');
    	$inspiration->setTitle('Another Link');
    	$inspiration->setDescription('This post was created from code!');
    	$inspiration->setType('url');
    	$inspiration->setDetail('http://google.com');
    	$inspiration->save();
    	echo 'post with ID ' . $inspiration->getId() . ' created';
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

public function showAllInspirationAction() {
    $posts = Mage::getModel('inspiration/inspiration')->getCollection();
    foreach($posts as $inspiration){
        echo '<h3>'.$inspiration->getTitle().'</h3>';
        echo nl2br($inspiration->getDescription());
    }
}
*/

}
