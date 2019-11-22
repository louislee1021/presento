<?php

namespace Nahid\Presento;

abstract class Presenter {
    /**
     * @var bool
     */
    protected $formatDatatables = false;
    /**
     * @var string|null
     */
    protected $transformer = null;

    /**
     * @var array|mixed
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $generatedData = [];

    /**
     * @var null
     */
    protected $default = null;

    /**
     * @var array
     */
    protected $presentScheme;

    /**
     * @var bool
     * @since v1.1
     */
    protected $isProcessed = false;

    public function __construct($data = null, $transformer = null) {
        $this->presentScheme = $this->present();
        $this->data = $this->init($data);

        $this->transformer = $this->transformer();
        if (!is_null($transformer)) {
            $this->transformer = $transformer;
        }
    }

    public function __invoke() {
        return $this->get();
    }

    public function __toString() {
        return json_encode($this->generatedData);
    }

    /**
     * @param array $present
     * @return $this
     */
    public function setPresent($present) {
        $this->presentScheme = $present;
        return $this;
    }

    /**
     * @param string $transformer
     * @return $this
     */
    public function setTransformer($transformer) {
        $this->transformer = $transformer;
        return $this;
    }

    /**
     * @return array
     * @since v1.1
     */
    public function getPresent() {
        return $this->presentScheme;
    }

    /**
     * @return string|null
     * @since v1.1
     */
    public function getTransformer() {
        return $this->transformer;
    }

    abstract public function present();

    /**
     * get transformer name, this method can be override
     *
     * @return null|string
     */
    public function transformer() {
        return null;
    }

    /**
     * @param $data
     * @return mixed
     * @since v1.1
     */
    public function init($data) {
        return $this->convert($data);
    }

    /**
     * @param $data
     * @return mixed
     *
     * @deprecated 1.1.0
     */
    public function convert($data) {
        return $data;
    }

    /**
     *
     * @param $data
     * @return mixed
     */
    public function map($data) {
        return $data;
    }

    /**
     * handle data based on presented data
     *
     * @return array
     */
    public function handle() {
        $this->isProcessed = true;

        return $this->handleDefault($this->map($this->data));

        // Commented this issue
        // if (is_collection($this->data)) {
        //     $generatedData = [];
        //     foreach ($this->data  as $property => $data) {
        //         $generatedData[$property] = $this->handleDefault($this->map($data));
        //     }
        //     return $generatedData;
        // }
    }

    protected function handleDefault($data) {
        if (!blank($data)) {
            return $this->transform($this->process($data));
        }

        if (is_array($this->default) && count($this->default) > 0) {
            $this->presentScheme = $this->default;
            return $this->transform($this->process($data));
        }

        return $this->default;
    }

    /**
     * process data based presented data model
     *
     *
     * @param array $data
     * @return array
     */
    public function process($data) {
        $present = $this->presentScheme;
        $record = [];

        // When there is no filter presented by the method present() of presenter
        if (count($present) == 0) {
            $record = $data;
        } else {
            foreach ($present as $key => $value) {
                if (is_numeric($key)) {
                    $key = $value;
                }

                if (is_array($value) && count($value) == 1) {
                    $class = array_keys($value)[0];
                    $params = $value[$class];
                    $arrData = null !== array_shift($params) ? array_shift($params) : '.';
                    $transformer = array_shift($params);
                    $args = [get_from_array($data, $arrData), $transformer] + $params;

                    $presenter = new $class(...$args);
                    $newVal = $value;
                    if ($presenter instanceof Presenter) {
                        $newVal = $presenter->handle();
                    }
                    
                    $this->formatDatatables ? ($record[] = $newVal) : ($record[$key] = $newVal);
                } else {
                    $this->formatDatatables ? ($record[] = $value ? get_from_array($data, $value) : $value) : ($record[$key] = $value ? get_from_array($data, $value) : $value);
                }
            }
        }

        return $record;
    }

    /**
     * transform given data based on transformer.
     *
     * @param array $data
     * @return array
     */
    protected function transform($data) {
        if (!is_array($data)) {
            return $data;
        }

        $transformerClass = $this->transformer;

        if (!is_null($transformerClass)) {
            $transformer = new $transformerClass($data);
            return $transformer();
        }

        return $data;
    }

    /**
     * get generated data as json string
     *
     * @return string
     */
    public function toJson() {
        return json_encode($this->get());
    }

    /**
     * get full set of data as array
     *
     */
    public function get() {
        if (!$this->isProcessed) {
            $this->generatedData = $this->handle();
        }

        return $this->generatedData;
    }
}