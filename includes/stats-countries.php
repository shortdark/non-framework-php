<?php

/**
 * CountriesClass - Displays country data from an XML as a table,  pie chart and world map.
 * Version 0.1.3
 * @package GRAPHClass
 * @link https://github.com/shortdark/gitme/
 * @author Neil Ludlow (shortdark) <neil@shortdark.net>
 * @copyright 2015 Neil Ludlow
 */


class CountriesClass extends StatsBaseClass
{
    /**
     * ########################
     * ##
     * ##  CONFIG
     * ##
     * ########################
     */


    /*
     * Location of the country XML file
     */
    private $country_xmlfile = '/countries/xml/units-per-country.xml';
    private $svg_file = '/countries/includes/html/world-map-svg.inc';

    private $color1 = "#cc0000";
    private $color2= "#cc2222";
    private $color3 = "#cc4444";
    private $color4 = "#cc6666";
    private $color5 = "#cc9999";
    private $remaining_color = "rgb(153,153,153)"; // color for the remainder of countries on pie chart
    private $color1_codes="";
    private $color2_codes="";
    private $color3_codes="";
    private $color4_codes="";
    private $color5_codes="";

    private $radius = 200;

    /**
     * ########################
     * ##
     * ##  CLASS VARIABLES
     * ##
     * ########################
     */


    /*
     * The output string... a world map with highlighted colors to show volume of units
     */
    public $highlighted_world_map = "";

    /*
     * The output string... a table showing the volumes of units per country
     */
    public $country_table_pie = "";

    /*
     * Set this to true for the additional jQuery will be included in the template
     */
    public $mapscript = false;

    /*
     * The page title is set from the PHP page
     */
    public $page_title = "";



    /*
     * XML of the main country data
     */
    private $country_data_from_xml = array();

    /*
     * The SVG is assembled and stored in this variable
     */
    private $svg_bit="";

    /*
     * The table is assembled and stored in this variable
     */
    private $table_bit="";

    /*
     * The table and pie chart show all the larger countries individually, then
     * combines all the others into "Remaining Countries"
     */
    private $remaining = array();

    /*
     * Variables used in assembling the pie chart
     */
    private $newx=0;
    private $newy=0;
    private $newx_old=0;
    private $newy_old=0;
    private $large = 0; // is 1 if the angle greater than 180 degrees
    private $startingline = ""; // the starting point of a segment of the pie chart SVG
    private $remaining_perc=0; // simmilar to $remaining but this is a number, not an array


    /**
     * ########################
     * ##
     * ##  MAIN METHODS
     * ##
     * ########################
     */

    /**
     * Put the data in a table and a pie chart.
     *
     * @param string $page_title
     */
    public function draw_country_list_from_xml($page_title=""){
        self::assign_page_title($page_title);
        self::grab_xml_data();
        self::make_table_from_xml();
        self::make_pie_chart_svg();
        self::draw_pie_and_table_to_page();
        return;
    }


    /**
     * Plot the data onto a world map.
     *
     * @param string $page_title
     */
    public function draw_world_map_from_xml($page_title=""){
        self::assign_page_title($page_title);
        self::grab_xml_data();
        self::draw_world_map_svg_from_xml();
        return;
    }


    /**
     * ########################
     * ##
     * ##  SHARED
     * ##
     * ########################
     */

    /**
     * @param string $page_title
     */
    private function assign_page_title($page_title=""){
        if($page_title){
            $this->page_title = filter_var($page_title, FILTER_SANITIZE_STRING);
        }
        return;
    }

    /**
     * Grab the data from the XML file
     *
     */
    private function grab_xml_data(){
        $xml=simplexml_load_file($_SERVER["DOCUMENT_ROOT"]. $this->country_xmlfile) or die("Error: Cannot create XML object");
        $x=0;
        if($xml->dpnt[$x]->name){
            while($xml->dpnt[$x]->name){
                if($xml->dpnt[$x]->vol){
                    $this->country_data_from_xml[$x]['id'] = intval($xml->dpnt[$x]->cntid);
                    $this->country_data_from_xml[$x]['code'] = strval($xml->dpnt[$x]->code);
                    $this->country_data_from_xml[$x]['name'] = strval($xml->dpnt[$x]->name);
                    $this->country_data_from_xml[$x]['vol'] = intval($xml->dpnt[$x]->vol);
                    $this->country_data_from_xml[$x]['perc'] = floatval($xml->dpnt[$x]->perc);
                    $this->country_data_from_xml[$x]['angle'] = floatval($xml->dpnt[$x]->angle);
                }
                $x++;
            }
        }

        return;
    }

    /**
     * ########################
     * ##
     * ##  TABLE
     * ##
     * ########################
     */


    /**
     *
     */
    private function make_table_from_xml(){
        $this->table_bit = "<table>";
        $this->table_bit .= "<thead>";
        $this->table_bit .= "<tr>";
        $this->table_bit .= "<td><strong>ID</strong></td>";
        $this->table_bit .= "<td><strong>Code</strong></td>";
        $this->table_bit .= "<td><strong>Name</strong></td>";
        $this->table_bit .= "<td><strong>Volume</strong></td>";
        $this->table_bit .= "<td><strong>Percentage</strong></td>";
        $this->table_bit .= "<td><strong>Pie Chart Angle</strong></td>";
        $this->table_bit .= "</tr>";
        $this->table_bit .= "</thead>";

        $y=0;
        while($this->country_data_from_xml[$y]['name']){
            if(0 < $this->country_data_from_xml[$y]['vol'] and 0.6 < $this->country_data_from_xml[$y]['perc']){
                $this->table_bit .= "<tr>";
                $this->table_bit .= "<td>{$this->country_data_from_xml[$y]['id']}</td>";
                $this->table_bit .= "<td>{$this->country_data_from_xml[$y]['code']}</td>";
                $this->table_bit .= "<td>{$this->country_data_from_xml[$y]['name']}</td>";
                $this->table_bit .= "<td>{$this->country_data_from_xml[$y]['vol']}</td>";
                $this->table_bit .= "<td>{$this->country_data_from_xml[$y]['perc']}%</td>";
                $this->table_bit .= "<td>{$this->country_data_from_xml[$y]['angle']}</td>";
                $this->table_bit .= "</tr>";
            }else{
                $this->remaining['vol'] += $this->country_data_from_xml[$y]['vol'];
                $this->remaining['perc'] += $this->country_data_from_xml[$y]['perc'];
                $this->remaining['angle'] += $this->country_data_from_xml[$y]['angle'];
            }
            $y++;
        }
        $this->table_bit .= "<tr>";
        $this->table_bit .= "<td></td>";
        $this->table_bit .= "<td></td>";
        $this->table_bit .= "<td>Remaining Countries</td>";
        $this->table_bit .= "<td>{$this->remaining['vol']}</td>";
        $this->table_bit .= "<td>{$this->remaining['perc']}%</td>";
        $this->table_bit .= "<td>{$this->remaining['angle']}</td>";
        $this->table_bit .= "</tr>";
        $this->table_bit .= "</table>";
        return;
    }

    /**
     * ########################
     * ##
     * ##  PIE CHART
     * ##
     * ########################
     */

    /**
     *
     */
    private function make_pie_chart_svg(){
        if(isset($this->country_data_from_xml[0]['id'])){
            $css_bit = "#countrypiechart{ width: 400px; height: 400px; } a .land:hover{ stroke:white; fill: green; }";

            $this->svg_bit = "<svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" version=\"1.1\" id=\"countrypiechart\"><circle cx=\"$this->radius\" cy=\"$this->radius\" r=\"$this->radius\" stroke=\"black\" stroke-width=\"1\" />\n";
            $this->svg_bit .= "<style type=\"text/css\">" . $css_bit . "</style>";

            self::make_pie_chart_segments();
        }

        return;
    }

    private function make_pie_chart_segments(){
        $testangle_orig=0;
        $y=0;
        while($this->country_data_from_xml[$y]['name']){
            if(0 < $this->country_data_from_xml[$y]['vol']){
                if(2 < $this->country_data_from_xml[$y]['angle']){
                    $prev_angle = $testangle_orig;
                    $testangle_orig += $this->country_data_from_xml[$y]['angle'];
                    self::get_absolute_coordinates_from_angle($testangle_orig,$prev_angle);

                    $color = self::assign_country_piechart_color($this->country_data_from_xml[$y]['perc']);

                    if($this->country_data_from_xml[$y]['code']){
                        $name = $this->country_data_from_xml[$y]['name'];
                        $safename = str_replace(" ","-", $name);
                        // Links disabled for demo
                        // $link = "xlink:href=\"$this->siteurl/country/$safename/\"";
                        $link = "xlink:href=\"#\"";
                    }else{
                        $link = "";
                    }
                    $this->svg_bit .= "  <a $link xlink:title=\"{$this->country_data_from_xml[$y]['name']}: {$this->country_data_from_xml[$y]['perc']}%\"><path id=\"{$this->country_data_from_xml[$y]['code']}\" class=\"land\" d=\"M$this->radius,$this->radius $this->startingline A$this->radius,$this->radius 0 $this->large,1 $this->newx,$this->newy z\" fill=\"$color\" stroke=\"black\" stroke-width=\"1\" fill-opacity=\"1\" /></a>\n";
                }else{
                    $this->remaining_perc += $this->country_data_from_xml[$y]['perc'];
                }
            }
            $y++;
        }

        $this->svg_bit .= "  <a xlink:title=\"Remaining Countries: $this->remaining%\"><path class=\"land\" d=\"M$this->radius,$this->radius L$this->newx,$this->newy A$this->radius,$this->radius 0 $this->large,1 $this->radius,0 z\" fill=\"$this->remaining_color\" stroke=\"black\" stroke-width=\"1\" fill-opacity=\"1\" /></a>\n";

        return;
    }

    /**
     * @param $testangle_orig
     * @param $prev_angle
     */
    private function get_absolute_coordinates_from_angle($testangle_orig, $prev_angle){
        if(270 < $testangle_orig){
            $testangle = $testangle_orig - 270;
            $quadrant = 4;
        }elseif(180 < $testangle_orig){
            $testangle = $testangle_orig - 180;
            $quadrant = 3;
        }elseif(90 < $testangle_orig){
            $testangle = $testangle_orig - 90;
            $quadrant = 2;
        }else{
            $testangle = $testangle_orig;
            $quadrant = 1;
        }
        if(180 < $testangle_orig - $prev_angle){
            $this->large = 1;
        }else{
            $this->large = 0;
        }
        if(0 < $prev_angle){
            $this->newx_old = $this->newx;
            $this->newy_old = $this->newy;
            $this->startingline = "L$this->newx,$this->newy";
        }else{
            $this->newx_old = 0;
            $this->newy_old = 0;
            $this->startingline = "V0";
        }
        if(1==$quadrant){
            $this->newx = $this->radius + ($this->radius * sin(deg2rad($testangle)));
            $this->newy = $this->radius - ($this->radius * cos(deg2rad($testangle)));
        }elseif(2==$quadrant){
            $this->newx = $this->radius + ($this->radius * cos(deg2rad($testangle)));
            $this->newy = $this->radius + ($this->radius * sin(deg2rad($testangle)));
        }elseif(3==$quadrant){
            $this->newx = $this->radius - ($this->radius * sin(deg2rad($testangle)));
            $this->newy = $this->radius + ($this->radius * cos(deg2rad($testangle)));
        }elseif(4==$quadrant){
            $this->newx = $this->radius - ($this->radius * cos(deg2rad($testangle)));
            $this->newy = $this->radius - ($this->radius * sin(deg2rad($testangle)));
        }
        return;
    }

    /**
     * @param int $perc
     * @return string
     */
    private function assign_country_piechart_color($perc=0){
        /*
         * Make something better than this
         */
        $othercolor=0;
        if(25 >= $perc){
            $othercolor = intval((25 - $perc)*10);
            $color = "rgb(255,$othercolor,$othercolor)";
        }else{
            $color = "rgb(153,$othercolor,$othercolor)";
        }
        return $color;
    }

    /**
     *
     */
    private function draw_pie_and_table_to_page(){
        $this->country_table_pie = $this->svg_bit;
        $this->country_table_pie .= $this->table_bit;
        $this->country_table_pie .= "<p>Some units will be assigned more than one country, this pie chart only uses the first country assigned to a unit.</p>";
        return;
    }


    /**
     * ########################
     * ##
     * ##  WORLD MAP
     * ##
     * ########################
     */

    /**
     * This displays the country data into a table
     *
     *
     */
    private function draw_world_map_svg_from_xml(){
        $svg_map = file_get_contents($_SERVER["DOCUMENT_ROOT"]. $this->svg_file, true);
        $css_bit = "#worldmap{ width: 1100px; height: 670px; background-color: #ccccff; }
            .land{ fill: #CCCCCC; fill-opacity: 1; stroke:black; stroke-opacity: 1; stroke-width:0.5; }
            a .land:hover{ stroke:white; }";

        $y=0;
        while($this->country_data_from_xml[$y]['name']){
            if(0 < $this->country_data_from_xml[$y]['vol'] and 2 == strlen($this->country_data_from_xml[$y]['code']) ){
                $code = $this->country_data_from_xml[$y]['code'];
                $name = $this->country_data_from_xml[$y]['name'];
                $perc = $this->country_data_from_xml[$y]['perc'];
                $vol = $this->country_data_from_xml[$y]['vol'];
                $safename = str_replace(" ","-", $name);
                // Links disabled for demo
                // $svg_map = preg_replace("~<path id=\"$code\" .*?/>~i","<a xlink:href=\"$this->siteurl/country/$safename/\" xlink:title=\"$name - $vol - $perc%\">$0</a>",$svg_map,1);

                $this->assign_country_a_color($code, $vol);
            }
            $y++;
        }
        $css_bit .= "$this->color1_codes {fill: $this->color1;} $this->color2_codes {fill: $this->color2;} $this->color3_codes {fill: $this->color3;} $this->color4_codes {fill: $this->color4;} $this->color5_codes {fill: $this->color5;}";
        $this->highlighted_world_map .= "<style type=\"text/css\">\n" . $css_bit . "\n</style>\n";
        $this->highlighted_world_map .= "<div class=\"test\"></div>";
        $this->highlighted_world_map .= $svg_map;

        // Add the additional map script to the template
        $this->mapscript = true;

        return;
    }

    private function assign_country_a_color($code="", $vol=0){
        /*
         * Replace this with something better to get the country colors...
         */
        if(999 < $vol){
            if(""==$this->color1_codes){
                $this->color1_codes = "#$code";
            }else{
                $this->color1_codes .= ", #$code ";
            }
        }elseif(299 < $vol){
            if(""==$this->color2_codes){
                $this->color2_codes = "#$code";
            }else{
                $this->color2_codes .= ", #$code ";
            }
        }elseif(99 < $vol){
            if(""==$this->color3_codes){
                $this->color3_codes = "#$code";
            }else{
                $this->color3_codes .= ", #$code ";
            }
        }elseif(49 < $vol){
            if(""==$this->color4_codes){
                $this->color4_codes = "#$code";
            }else{
                $this->color4_codes .= ", #$code ";
            }
        }else{
            if(""==$this->color5_codes){
                $this->color5_codes = "#$code";
            }else{
                $this->color5_codes .= ", #$code ";
            }
        }
        return;
    }





}


