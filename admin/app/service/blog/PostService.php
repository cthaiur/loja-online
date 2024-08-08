<?php
class PostService
{
    /**
     * Render a given post
     */
    public static function render($post, $template)
    {
        $content = '';

        $result = $template;
        $user   = $post->user;

        $result = str_replace('{title}', $post->title, $result);
        $result = str_replace('{subtitle}', $post->subtitle, $result);
        $result = str_replace('{slug}', TextTools::slug($post->title), $result);
        $result = str_replace('{id}', $post->id, $result);
        $result = str_replace('{author}', $post->user_name, $result);
        $result = str_replace('{image}', $post->image, $result);
        $result = str_replace('{login}', $post->login, $result);
        $result = str_replace('{post_date}', $post->post_date, $result);
        $result = str_replace('{past_time}', TimeTools::getShortPastTime($post->post_date), $result);
        $result = str_replace('{content}', $post->content, $result);
        $result = str_replace('{author_photo}', "app/images/photos/{$user->login}.jpg", $result);
        $result = str_replace('{category_name}', $post->category_name, $result);
        
        return $result;
    }
    
    public static function renderMedia($post)
    {
        $media = $post->media;
        
        if ($media)
        {
            if ($media->type == 'youtube')
            {
                $content = file_get_contents('app/resources/youtube.html');
                foreach ($media->toArray() as $key => $value)
                {
                    $content = str_replace('{'.$key.'}', $value, $content);
                }
                return $content;
            }
            else if ($media->type == 'slideshare')
            {
                $content = file_get_contents('app/resources/slideshare.html');
                foreach ($media->toArray() as $key => $value)
                {
                    $content = str_replace('{'.$key.'}', $value, $content);
                }
                return $content;
            }
        }
        
        return '';
    }
    
    /**
     * Render a collection of posts
     */
    public static function renderMany($posts, $template)
    {
        $content = '';
        if ($posts)
        {
            foreach ($posts as $post)
            {
                $content .= self::render($post, $template);
            }
        }
        return $content;
    }
    
    /**
     * Render years
     */
    public static function renderYears($template)
    {
        $content = '';
        $years = Post::getYears();
        foreach ($years as $year)
        {
            $content .= str_replace('{year}', $year, $template);
        }
        
        return $content;
    }
    
    /**
     * Render tags
     */
    public static function renderTags($template, $category_id = null)
    {
        $content = '';
        $tags = Post::getTags( $category_id );
        foreach ($tags as $tag)
        {
            if (trim($tag))
            {
                $content .= str_replace('{tag}', $tag, $template);
            }
        }
        
        return $content;
    }
}
