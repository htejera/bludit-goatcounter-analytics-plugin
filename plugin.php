<?php

class pluginGoatCounter extends Plugin
{
    public function init()
    {
        $this->dbFields = array(
            'endpoint'      => '',
            'show_counter'  => false,
            'counter_type'  => 'html', // html, svg, png
            'css_selector'  => '',
            'no_branding'   => false,
            'counter_style' => ''
        );
    }

    public function adminController()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
    
        global $security;
        $token = isset($_POST['tokenCSRF']) ? $_POST['tokenCSRF'] : '';
        if (!$security->validateTokenCSRF($token)) {
            return false;
        }
    
        $endpoint      = isset($_POST['endpoint']) ? trim($_POST['endpoint']) : '';
        $show_counter  = isset($_POST['show_counter']) ? true : false;
        $counter_type  = isset($_POST['counter_type']) ? $_POST['counter_type'] : 'html';
        $css_selector  = isset($_POST['css_selector']) ? trim($_POST['css_selector']) : '';
        $no_branding   = isset($_POST['no_branding']) ? true : false;
        $counter_style = isset($_POST['counter_style']) ? trim($_POST['counter_style']) : '';
    
        $this->setValue('endpoint',      $endpoint);
        $this->setValue('show_counter',  $show_counter ? true : false);
        $this->setValue('counter_type',  $counter_type);
        $this->setValue('css_selector',  $css_selector);
        $this->setValue('no_branding',   $no_branding ? true : false);
        $this->setValue('counter_style', $counter_style);
    
        return true;
    }
    
    public function form()
    {
        global $security;
        global $L;

        $tokenCSRF = $security->getTokenCSRF();

        $endpoint     = htmlspecialchars($this->getValue('endpoint'), ENT_QUOTES, 'UTF-8');
        $show_counter = $this->getValue('show_counter') ? 'checked' : '';
        $counter_type = htmlspecialchars($this->getValue('counter_type'), ENT_QUOTES, 'UTF-8');
        $css_selector = htmlspecialchars($this->getValue('css_selector'), ENT_QUOTES, 'UTF-8');
        $no_branding  = $this->getValue('no_branding') ? 'checked' : '';
        $counter_style= htmlspecialchars($this->getValue('counter_style'), ENT_QUOTES, 'UTF-8');

        $html  = '<div class="alert alert-primary" role="alert">';
		$html .= $this->description();
		$html .= '</div>';

        $html .= '<form method="post">';
        $html .= '<input type="hidden" name="tokenCSRF" value="'.$tokenCSRF.'">';

        $html .= '<div class="form-group">';
        $html .= '<label for="endpoint">' . $L->get('endpoint') . '</label>';
        $html .= '<input id="endpoint" name="endpoint" type="text" class="form-control" value="'.$endpoint.'" placeholder="https://CODE.goatcounter.com/count">';
        $html .= '<small class="form-text text-muted">Ej: https://yoursite.goatcounter.com/count</small>';
        $html .= '</div>';

        // Mostrar contador (hidden + checkbox)
        $valShow = $this->getValue('show_counter');
        $checkedShow = ( $valShow === true || $valShow === 'true' || $valShow === '1' || $valShow === 1 ) ? 'checked' : '';
        $html .= '<input type="hidden" name="show_counter" value="0">'; // garantiza envío si está desmarcado
        $html .= '<input id="show_counter" name="show_counter" type="checkbox" value="1" class="form-check-input" '.$checkedShow.'>';
        $html .= '<label for="show_counter" class="form-check-label">'. $L->get('show_counter') .'</label>';

        
        $html .= '<div class="form-group">';
        $html .= '<label for="counter_type">'. $L->get('counter_type') .'</label>';
        $html .= '<select id="counter_type" name="counter_type" class="form-control">';
        $options = ['html' => 'HTML (iframe)', 'svg' => 'SVG (imagen)', 'png' => 'PNG (imagen)'];
        foreach ($options as $val => $label) {
            $sel = ($counter_type === $val) ? 'selected' : '';
            $html .= '<option value="'.$val.'" '.$sel.'>'.$label.'</option>';
        }
        $html .= '</select>';
        $html .= '</div>';

        $html .= '<div class="form-group">';
        $html .= '<label for="css_selector">'. $L->get('css_selector') .'</label>';
        $html .= '<input id="css_selector" name="css_selector" type="text" class="form-control" value="'.$css_selector.'" placeholder=".mi-clase, #mi-id">';
        $html .= '</div>';

        // No branding (hidden + checkbox)
        $valNo = $this->getValue('no_branding');
        $checkedNoBrand = ( $valNo === true || $valNo === 'true' || $valNo === '1' || $valNo === 1 ) ? 'checked' : '';
        $html .= '<input type="hidden" name="no_branding" value="0">'; // garantiza envío si está desmarcado
        $html .= '<input id="no_branding" name="no_branding" type="checkbox" value="1" class="form-check-input" '.$checkedNoBrand.'>';
        $html .= '<label for="no_branding" class="form-check-label">'. $L->get('no_branding') .'</label>';

        $html .= '<div class="form-group">';
        $html .= '<label for="counter_style">'. $L->get('css_selector') .'</label>';
        $html .= '<input id="counter_style" name="counter_style" type="text" class="form-control" value="'.$counter_style.'" placeholder="width:120px; text-align:center;">';
        $html .= '</div>';

        $html .= '<button type="submit" class="btn btn-primary">'. $L->get('save') .'</button>';
        $html .= '</form>';

        return $html;
    }

    public function siteHead()
    {
        $endpoint = trim($this->getValue('endpoint'));

        if (empty($endpoint)) {
            return false;
        }

        $endpoint = rtrim($endpoint, '/');

        $endpointAttr = htmlspecialchars($endpoint, ENT_QUOTES, 'UTF-8');

        $out  = "\n<!-- GoatCounter injected by pluginGoatCounter -->\n";
        $out .= '<script data-goatcounter="'.$endpointAttr.'" async src="//gc.zgo.at/count.js"></script>' . PHP_EOL;

        // Theme::plugins('siteHead')
        echo $out;
    }

    public function siteBodyEnd()
    {
        $rawEndpoint = trim($this->getValue('endpoint'));
        if (empty($rawEndpoint)) {
            return false;
        }

        $endpointAttr = htmlspecialchars($rawEndpoint, ENT_QUOTES, 'UTF-8');
    
        $out  = "\n<!-- GoatCounter injected by pluginGoatCounter -->\n";
        $out .= '<script data-goatcounter="'.$endpointAttr.'" async src="//gc.zgo.at/count.js"></script>' . PHP_EOL;
    
        if (!$this->getValue('show_counter')) {
            echo $out;
            return;
        }
    
        if (!preg_match('#^https?://#i', $rawEndpoint)) {
            $base = 'https://' . preg_replace('#(^https?://|/count$)#i', '', $rawEndpoint) . '.goatcounter.com';
        } else {
            $base = preg_replace('#/count/?$#i', '', $rawEndpoint);
        }
    
        $type = in_array($this->getValue('counter_type'), ['html','svg','png']) ? $this->getValue('counter_type') : 'html';
        $selector = $this->getValue('css_selector') ?: '';
        $noBranding = $this->getValue('no_branding') ? 1 : 0;
        $styleInline = htmlspecialchars($this->getValue('counter_style'), ENT_QUOTES, 'UTF-8');
    
        $containerId = 'gc-counter-container';
        $initialHtml = '<div id="'.$containerId.'" style="'.$styleInline.'">Cargando contador…</div>';
    
        $baseJs = json_encode($base);
        $selectorJs = json_encode($selector);
        $typeJs = json_encode($type);
        $noBrandingJs = ($noBranding ? 'true' : 'false');
    
        $js = <<<EOT
    <script>
    (function(){
      try {
        var base = {$baseJs};
        var ext = {$typeJs};
        var selector = {$selectorJs};
        var no_branding = {$noBrandingJs};
        var container = document.getElementById('{$containerId}');
        if (!container) return;
    
        function buildCounterUrl(path) {
          if (!path) path = '/';
          if (!path.startsWith('/')) path = '/' + path;
          var url = base + '/counter/' + encodeURIComponent(path) + '.' + ext;
          var params = [];
          if (no_branding) params.push('no_branding=1');
          if (params.length) url += '?' + params.join('&');
          return url;
        }
    
        var path = window.location.pathname || '/';
        var url = buildCounterUrl(path);
    
        if (ext === 'html') {
          var iframe = document.createElement('iframe');
          iframe.src = url;
          iframe.style.border = '0';
          iframe.style.width = '100%';
          iframe.style.height = '60px';
          iframe.loading = 'lazy';
          container.innerHTML = '';
          container.appendChild(iframe);
        } else {
          var img = document.createElement('img');
          img.src = url;
          img.alt = "Visitor counter";
          img.loading = 'lazy';
          container.innerHTML = '';
          container.appendChild(img);
        }
    
        if (selector) {
          try {
            var target = document.querySelector(selector);
            if (target) {
              target.appendChild(container);
            } else {
              console.warn('GoatCounter plugin: selector not found:', selector);
            }
          } catch (e) {
            console.warn('GoatCounter plugin: invalid selector:', selector);
          }
        }
    
        // visit_count extra
        var t = setInterval(function() {
          if (window.goatcounter && typeof window.goatcounter.visit_count === 'function') {
            clearInterval(t);
            try {
              window.goatcounter.visit_count({
                append: selector || ('#{$containerId}'),
                type: ext,
                no_branding: no_branding ? 1 : 0
              });
            } catch (ex) {
              console.warn('GoatCounter visit_count failed:', ex);
            }
          }
        }, 100);
    
      } catch (e) {
        console.error('GoatCounter plugin error', e);
      }
    })();
    </script>
    EOT;
    
        echo $out . $initialHtml . $js;
    }

    public function adminSidebar()
    {
        $pluginName = Text::lowercase(__CLASS__);
        $url = HTML_PATH_ADMIN_ROOT.'plugin/'.$pluginName;
        $html = '<a id="gc-plugin" class="nav-link" href="'.$url.'">GoatCounter</a>';
        return $html;
    }
}

?>
