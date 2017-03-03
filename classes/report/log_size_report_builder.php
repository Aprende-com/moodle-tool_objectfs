<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Log size report
 *
 * @package   tool_objectfs
 * @author    Kenneth Hendricks <kennethhendricks@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_objectfs\report;

defined('MOODLE_INTERNAL') || die();

class log_size_report_builder extends objectfs_report_builder {

    public function build_report() {
        global $DB;

        $report = new objectfs_report('log_size');

        $sql = 'SELECT log as datakey,
                       sum(filesize) as objectsum,
                       count(*) as objectcount
                  FROM (SELECT DISTINCT contenthash, filesize, floor(log(2,filesize)) AS log
                            FROM {files}
                            WHERE filesize != 0) d
              GROUP BY log ORDER BY log';

        $results = $DB->get_records_sql($sql);

        $smallcount = 0;
        $smallsum = 0;

        foreach ($results as $key => $result) {
            // Logsize means that files are smaller than 1 mb
            if ($result->datakey <= 19) {
                $smallcount += $result->objectcount;
                $smallsum += $result->objectsum;
                unset($results[$key]);
            }
        }

        $report->add_row('small', $smallcount, $smallsum);

        $report->add_rows($results);

        return $report;
    }
}