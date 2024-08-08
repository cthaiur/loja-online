<?php
class CategoryService
{
    /**
     * Render the category index
     */
    public static function render($template, $current_id = null)
    {
        $categories = Category::where('show_menu', '=', 'Y')->orderBy('position')->load();
        
        $content = '';
        if ($categories)
        {
            foreach ($categories as $category)
            {
                $result = $template;
                $result = str_replace('{id}',    $category->id,    $result);
                $result = str_replace('{name}',  $category->name,  $result);
                $result = str_replace('{icon}',  (string) $category->icon,  $result);
                $result = str_replace('{image}', (string) $category->image, $result);
                $result = str_replace('{slug}',  TextTools::slug($category->name), $result);
                if (!empty($category->link))
                {
                    $result = str_replace('{link}',$category->link, $result);
                }
                else if (!file_exists('../.htaccess'))
                {
                    $result = str_replace('{link}',"index.php?action=show_category&category_id={$category->id}", $result);
                }
                else
                {
                    $result = str_replace('{link}',TextTools::slug($category->name), $result);
                }
                
                if (!empty($current_id) && $current_id == $category->id)
                {
                    $result = str_replace('{class}', 'current_page_item', $result);
                }
                else
                {
                    $result = str_replace('{class}', 'page_item', $result);
                }
                $content .= $result;
            }
        }
        return $content;
    }
}
