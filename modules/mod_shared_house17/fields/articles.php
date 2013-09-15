<?php 
defined('JPATH_BASE') or die();
error_reporting(0); 
jimport('joomla.html.html');
jimport('joomla.form.formfield');//import the necessary class definition for formfield


/**
 * Supports an HTML select list of articles
 * @since 1.6
 */
class JFormFieldarticles extends JFormField
{
 
   protected $type = 'articles'; //the form field type

 /**
 * Method to get content articles
 *
 * @return array The field option objects.
 * @since 1.6
 */
 protected function getInput()
 {
    $db = &JFactory::getDBO();

    $session = JFactory::getSession();
 $options = array();
 
 $attr = '';

 // Initialize some field attributes.
 $attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

 // To avoid user's confusion, readonly="true" should imply disabled="true".
 if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
 $attr .= ' disabled="disabled"';
 }

 $attr .= 'width="200px"';
 $attr .= $this->multiple ? ' multiple="multiple"' : '';

 // Initialize JavaScript field attributes.
 $attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
 

   
      // This might get a conflict with the dynamic translation
      // - TODO: search for better solution
       //now get to the business of finding the articles
 
 $db = &JFactory::getDBO();
 $query = 'SELECT * FROM #__categories WHERE published=1 ORDER BY parent_id';
 $db->setQuery( $query );
 $categories = $db->loadObjectList();
  $articles=array();
 
 // set up first element of the array as all articles
 $articles[0]->id = '';
 $articles[0]->title = JText::_("Select Article");
 
 //loop through categories 
 foreach ($categories as $category) {
 $optgroup = JHTML::_('select.optgroup',$category->title,'id','title');
 $query = 'SELECT id,title FROM #__content WHERE catid='.$category->id;
 $db->setQuery( $query );
 $results = $db->loadObjectList();
 if(count($results)>0)
 {
 array_push($articles,$optgroup);
 foreach ($results as $result) {
 array_push($articles,$result);
 }
 }
 } 
 
 // Output
 
 return JHTML::_('select.genericlist', $articles, $this->name, trim($attr), 'id', 'title', $this->value );
 
  
  }
}
?>
