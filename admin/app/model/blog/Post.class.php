<?php
/**
 * Post Active Record
 * @author  <your-name-here>
 */
class Post extends TRecord
{
    const TABLENAME = 'post';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    use SystemChangeLogTrait;
    
    private $category;
    private $media;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('title');
        parent::addAttribute('subtitle');
        parent::addAttribute('content');
        parent::addAttribute('login');
        parent::addAttribute('post_date');
        parent::addAttribute('tags');
        parent::addAttribute('image');
        parent::addAttribute('category_id');
        parent::addAttribute('media_id');
    }
    
    /**
     * Method get_media
     * @returns Media instance
     */
    public function get_media()
    {
        // loads the associated object
        if (empty($this->media))
        {
            $this->media = new Media($this->media_id);
        }
        
        // returns the associated object
        return $this->media;
    }
    
    public function get_category()
    {
        // loads the associated object
        if (empty($this->category))
        {
            $this->category = new Category($this->category_id);
        }
        
        // returns the associated object
        return $this->category;
    }
    
    /**
     * Method get_category_name
     * Sample of usage: $post->category->attribute;
     * @returns Category instance
     */
    public function get_category_name()
    {
        // loads the associated object
        if (empty($this->category))
        {
            $this->category = new Category($this->category_id);
        }
        
        // returns the associated object
        return $this->category->name;
    }
    
    public function get_user_name()
    {
        if (!empty($this->login))
        {
            TTransaction::open('permission');
            $user = SystemUser::newFromLogin( $this->login );
            TTransaction::close();
            return $user->name;
        }
    }

    public function get_user()
    {
        if (!empty($this->login))
        {
            TTransaction::open('permission');
            $user = SystemUser::newFromLogin( $this->login );
            TTransaction::close();
            return $user;
        }
    }

    /**
     * Return years with posts
     */
    public static function getYears()
    {
        $years = [];
        
        $conn = TTransaction::get(); // get PDO connection
        
        $info = TTransaction::getDatabaseInfo();
        
        $function['sqlite'] = "strftime('%Y', post_date)";
        $function['pgsql']  = "date_part('Y', post_date)";
        $function['mysql']  = "EXTRACT(YEAR FROM post_date)";
        
        $instruction = $function[ $info['type'] ];
        
        // run query
        $result = $conn->query("select distinct $instruction as year from post where post_date is not null order by year desc");
        
        foreach ($result as $row)
        {
            $years[] = $row['year'];
        }
        
        return $years;
    }
    
    /**
     * Return posts of the year
     */
    public static function getForYear($year)
    {
        $info = TTransaction::getDatabaseInfo();
        
        $function['sqlite'] = "strftime('%Y', post_date)";
        $function['pgsql']  = "date_part('Y', post_date)";
        $function['mysql']  = "EXTRACT(YEAR FROM post_date)";
        
        $instruction = $function[ $info['type'] ];
        
        return Post::where($instruction, '=', $year)->load();
    }
    
    /**
     * Return posts of the year
     */
    public static function getForTag($tag)
    {
        return Post::where('tags', 'like', '%' . $tag . ',%')->orWhere('tags', 'like', '%,' . $tag . '%')->orWhere('tags', '=', $tag)->load();
    }
    
    /**
     * Return post tags
     */
    public static function getTags( $category_id = null)
    {
        $tags = [];
        
        $conn = TTransaction::get(); // get PDO connection
        // run query
        if (!empty($category_id))
        {
            $sth = $conn->prepare("SELECT distinct tags FROM post WHERE tags is not null and category_id = ? ORDER BY tags");
            $sth->execute(array($category_id));
            $result = $sth->fetchAll();
        }
        else
        {
            $result = $conn->query("SELECT distinct tags FROM post WHERE tags is not null ORDER BY tags");
        }
        
        foreach ($result as $row)
        {
            $parts = explode(',', $row['tags']);
            foreach ($parts as $part)
            {
                $tags[] = $part;
            }
        }
        
        return array_unique($tags);
    }
    
}
