<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VHBpa0w0dWZpcldLb1NmNy9Za2pFZTdwbHpuRVpCZDF5bngzVEx0THlFOHpIYUVUejNwSEtuUnJUQXNYTmRoMDZnVm9tTzNUM2RyNUpEY3oxSlFra2tRekhSeVBKbEZUdEtHaENwZHdRREY5dFF0VDdrN01mVEplZTBxRlhGbXRLclVxUXBMM1h6ODlCczZBSVFZRWhk*/

namespace Aws;

use Psr\Http\Message\ResponseInterface;

interface ResponseContainerInterface
{
    /**
     * Get the received HTTP response if any.
     *
     * @return ResponseInterface|null
     */
    public function getResponse();
}