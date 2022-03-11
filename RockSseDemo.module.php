<?php namespace ProcessWire;
class RockSseDemo extends WireData implements Module, ConfigurableModule {

  public static function getModuleInfo() {
    return [
      'title' => 'RockSseDemo',
      'version' => '0.0.1',
      'summary' => 'Your module description',
      'autoload' => true,
      'singular' => true,
      'icon' => 'smile-o',
      'requires' => [],
      'installs' => [],
    ];
  }

  public function init() {
    $this->addHookAfter("/sse", $this, "serve");
  }

  /**
   * Serve the sse request
   */
  public function serve(HookEvent $event) {
    header("Cache-Control: no-cache");
    header("Content-Type: text/event-stream");

    $i = 0;
    while(++$i) {
      $this->sse("value of i = $i");
      if($i>=10) return $this->sse("DONE");
      while(ob_get_level() > 0) ob_end_flush();
      if(connection_aborted()) break;
      sleep(1);
    }
  }

  /**
   * Send SSE message to client
   * @return void
   */
  public function sse($msg) {
    echo "data: $msg\n\n";
    echo str_pad('',8186)."\n";
    flush();
  }

  /**
  * Config inputfields
  * @param InputfieldWrapper $inputfields
  */
  public function getModuleConfigInputfields($inputfields) {
    $inputfields->add([
      'type' => 'markup',
      'label' => 'demo',
      'value' => "
        <script>
        function sse() {
          const evtSource = new EventSource('/sse', { withCredentials: true } );
          evtSource.onmessage = function(event) {
            console.log(event);
            document.getElementById('log').innerText = event.data;
            if(event.data==='DONE') evtSource.close();
          }
        }
        </script>
        <p class='uk-button uk-button-primary' onclick='sse()'>count to 10</p>
        <p id='log'>Watch console!</p>
      ",
    ]);
    return $inputfields;
  }

}
