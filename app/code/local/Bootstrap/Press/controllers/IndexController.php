<?php
class Bootstrap_Press_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {    
		$this->loadLayout();
    	$this->renderLayout();
    }
	public function ListAction ()
	{
     	echo 'test list method';
    	$params = $this->getRequest()->getParams();
    	$press = Mage::getModel('press/press');
    	echo("Loading the press with an ID of ".$params['id']);
    	$press->load($params['id']);
    	$data = $press->getData();
    	var_dump($data);
    }
	public function createNewPressAction() {
    	$press = Mage::getModel('press/press');
    	$press->setTitle('Another Link');
    	$press->setDescription('This post was created from code!');
    	$press->setType('url');
    	$press->setDetail('http://google.com');
    	$press->save();
    	echo 'post with ID ' . $press->getId() . ' created';
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

public function showAllPressAction() {
    $posts = Mage::getModel('press/press')->getCollection();
    foreach($posts as $press){
        echo '<h3>'.$press->getTitle().'</h3>';
        echo nl2br($press->getDescription());
    }
}
*/

}
