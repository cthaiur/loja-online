<?php
/**
 * Category Active Record
 * @author  <your-name-here>
 */
class Category extends TRecord
{
    const TABLENAME = 'category';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('image');
        parent::addAttribute('link');
        parent::addAttribute('position');
        parent::addAttribute('icon');
        parent::addAttribute('show_menu');
        parent::addAttribute('description');
        
    }
    
    public function getPosts($offset, $limit)
    {
        return Post::where('category_id', '=', $this->id)->take($limit)->skip($offset)->orderBy('post_date desc, id', 'desc')->load();
    }
}
