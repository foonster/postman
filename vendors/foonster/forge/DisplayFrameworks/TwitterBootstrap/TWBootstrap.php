<?php
/**
 * @author Nicolas Colbert <nicolas@foonster.com>
 * @copyright 2005 Foonster Technology
 */
/**
 *                                                                   
 * A twitter bootstrap abstraction class
 * 
*                                                                    
 */
class TWBootstrap
{
    /**
     * @ignore 
     */ 
    public function __construct()
    {
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
    }   
    /**
     * 
     * return a well-formed HTML mark-up to insert an alert
     * @param string $message the string to be placed in the body.
     * @param string $type the type of alert that you want to display [success = default, info, warning, danger]
     * 
     * @return string
     */ 
    public static function alert($message, $type = 'success', $id = '')
    {        
        if ($type != 'success' && $type != 'warning' && $type != 'danger') {
            $type = 'success';
        }
        empty($id) ? $id = $type . 'Alert' : false;
        $string = '<div class="alert alert-' . $type . ' alert-dismissible flash" id="' . $id . '" role="alert">' . $message . '</div>';
        return $string;
    }
    /**
     * [tablist description]
     * @param  array  $options [description]
     * @return [type]          [description]
     */
    public static function breadcrumbs ($options = array(), $selected = '', $element = 'li') { 
        $links = array();
        if (empty($selected) || !array_key_exists($selected, $options)) { 
            reset($options);
            $first_key = key($options);            
        } else { 
            $first_key = $selected;
        }
        
        foreach ($options as $key => $option) { 
            if ($first_key == $key) { 
                $links[] = '<a href="#' 
                . $option['target'] 
                . '" class="breadcrumb-item active">' 
                . $option['label'] . '</a>';
            } else { 
                $links[] = '<a href="#' 
                . $option['target'] 
                . '" class="breadcrumb-item">' 
                . $option['label'] . '</a>';
            }
        }
        return '<nav class="breadcrumb">' . implode(' ', $links) . '</nav>';        
    }
                            
    /**
     * [radio description]
     * @param  array  $options [description]
     * @param  string $value   [description]
     * @return [type]          [description]
     */
    public static function radio($options = array(), $defaultValue = '', $display = '<br />')
    {
        $string = '';
        if (strtolower(trim($display)) == 'btn-group') { 
            $string .= '<div class="btn-group" data-toggle="buttons">';
            foreach ($options as $n => $option) { 
                $string .= '<label class="btn btn-primary';
                ($option['value'] == $defaultValue) ? $string .= ' active' : false;
                $string .= '" for="' . $option['name'] . '">';
                $string .= '<input type="radio" name="' . $option['name'] . '" value="' . $option['value'] . '"';
                ($option['value'] == $defaultValue) ? $string .= ' checked="checked"' : false;
                $string .= '>';
                $string .= $option['display'];
                $string .= '</label>';
            }
            $string .= '</div>';
        } else { 
            $radioList = array();
            foreach ($options as $n => $option) { 
                $tmp = '<input type="radio" name="' . $option['name'] . '" value="' . $option['value'] . '"';
                ($option['value'] == $defaultValue) ? $tmp .= ' checked="checked"' : false;
                $tmp .= '>&nbsp;' . $option['display'];
                $radioList[] = $tmp;
            }
            $string .= implode($display, $radioList);   
        }
        return $string;
    }
    /**
     * [tablist description]
     * @param  array  $options [description]
     * @return [type]          [description]
     */
    public static function tablist ($options = array(), $selected = '', $element = 'li') { 
        $links = array();
        if (empty($selected) || !array_key_exists($selected, $options)) { 
            reset($options);
            $first_key = key($options);            
        } else { 
            $first_key = $selected;
        }
        
        foreach ($options as $key => $option) { 
            if ($first_key == $key) { 
                $links[] = '<' . strtolower(trim($element)) . ' class="nav-item">'
                . '<a href="#' . $key . '" class="nav-link active" data-toggle="tab" role="tab">' . $option['label'] . '</a>' 
                . '</' . strtolower(trim($element)) . '>';
            } else { 
                $links[] = '<' . strtolower(trim($element)) . ' class="nav-item">'
                . '<a href="#' . $key . '" class="nav-link" data-toggle="tab" role="tab">' . $option['label'] . '</a>' 
                . '</' . strtolower(trim($element)) . '>';
            }
        }
        return '<ul class="nav nav-tabs" role="tablist">' . implode(' ', $links) . '</ul>';        
    }


}
