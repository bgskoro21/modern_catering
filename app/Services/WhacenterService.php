<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhacenterService
{

    protected string $to;
    protected array $lines;
    protected string $baseUrl = '';
    protected string $deviceId = '';


    /**
     * constructor.
     * @param array $lines
     */
    public function __construct($lines = [])
    {
        $this->lines = $lines;
        $this->baseUrl = 'https://app.whacenter.com/api';
        $this->deviceId = 'fd93567f732a90ffc182d0fdd2d26595';
    }

    public function getDeviceStatus()
    {
        return Http::get($this->baseUrl . '/statusDevice?device_id=' . $this->deviceId);
    }

    public function line($line = ''): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function to($to): self
    {
        $this->to = $to;

        return $this;
    }

    public function send(): mixed
    {
        if ($this->to == '' || count($this->lines) <= 0) {
            throw new \Exception('Message not correct.');
        }
        $params = 'device_id=' . $this->deviceId . '&number=' . $this->to . '&message=' . urlencode(implode("\n", $this->lines));
        $response = Http::post($this->baseUrl . '/send', [
            'device_id' => $this->deviceId,
            'number' => $this->to,
            'message' => implode("\n",$this->lines)
        ]);
        return $response->body();
    }
}



?>