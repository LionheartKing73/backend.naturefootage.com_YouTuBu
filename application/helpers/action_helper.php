<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('get_actions'))
{
	function get_actions($actions, $echo = true)
	{
        if(count($actions))
        {
            $out = '<div class="btn-group">
            <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                Action <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">';
            foreach ($actions as $action)
            {
                if ($action['display']){
                    if($action['name'] == 'Edit'){
                        $out .= '<li><a href="' . $action['url'] . '"><span data-action="' . $action['url'] . '" data-k="'.$action['keyword'].'" data-c="'.$action['collection'].'" data-s="'.$action['section'].'" class="edit ajaxify">'
                            . $action['name'] . '</span></a></li>';
                    }else{
                        $confirm = strstr($action['url'], 'delete') || strstr($action['url'], 'move') ?
                        ' onclick="return confirm(\'' . $action['confirm'] . '\')"' : '';
                        $out .= '<li><a href="' . $action['url'] . '"' . $confirm . '>'
                        . $action['name'] . '</a></li>';
                    }
                }
            }
            $out .= '</ul></div>';
            if($echo)
                echo $out;
            else
                return $out;
        }
	}

    function get_ajaxify_actions($actions)
    {
        if(count($actions))
        {
            $out = '<div class="btn-group">
            <button class="btn dropdown-toggle" data-toggle="dropdown">
                Action <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">';
            foreach ($actions as $action)
            {
                if ($action['display'])
                {
                    if($action['name'] == 'edit'){
                        $out .= '<li><span data-action="' . $action['url'] . '" data-k="'.$action['keyword'].'" data-c="'.$action['collection'].'" data-s="'.$action['section'].'" id="edit" class="ajaxify">'
                            . $action['name'] . '</span></li>';
                    }else{
                        $confirm = strstr($action['url'], 'delete') || strstr($action['url'], 'move') ?
                            ' onclick="return confirm(\'' . $action['confirm'] . '\')"' : '';
                        $out .= '<li><a href="' . $action['url'] . '"' . $confirm . ' class="ajaxify">'
                            . $action['name'] . '</a></li>';
                    }
                }
            }
            $out .= '</ul></div>';
            echo $out;
        }
    }
}

if ( ! function_exists('get_frontend_actions'))
{
    function get_frontend_actions($actions)
    {
        if(count($actions))
        {
            $out = "";
            foreach ($actions as $action)
            {
                if ($action['display'])
                {
                    if (strstr($action['url'], 'delete') || strstr($action['url'], 'move'))
                    {
                        $out .= '<a href='.$action['url'].' class="mand" onclick="return confirm(\''.$action['confirm'].'\');">'.$action['name'].'</a> | ';
                    }
                    else
                    {
                        $out .= '<a href='.$action['url'].' class="action">'.$action['name'].'</a> ';
                    }
                }
            }
            $out = substr($out, 0, strlen($out)-2);
            echo $out;
        }
    }
}
