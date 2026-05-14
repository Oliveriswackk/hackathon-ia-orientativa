/**
 * @typedef {{
 *  id?: string,
 *  doc_type: string,
 *  proceso?: string,
 *  reglas?: Record<string, unknown>,
 *  autoridad_default?: string,
 *  ruta_default?: string,
 *  tramite?: { tipo?: string, descripcion?: string },
 *  links_oficiales?: string[]
 * }} RulePack
 * @typedef {{ id: string, label: string, keywords?: string[] }} OfflineOption
 * @typedef {{ id: string, prompt: string, options: OfflineOption[] }} OfflineQuestion
 * @typedef {{
 *  tipo_doc: string,
 *  autoridad_competente: string,
 *  ruta_sugerida: string,
 *  tramite: string,
 *  modalidad: string,
 *  plazo: string,
 *  consecuencias: string,
 *  links_oficiales: string[],
 *  respuesta: string
 * }} OutputFormat
 * @typedef {{
 *  id: string,
 *  title: string,
 *  keywords: string[],
 *  summary: string,
 *  intentId?: string,
 *  doc_type?: string,
 *  proceso?: string,
 *  rule_pack_id?: string,
 *  rule_pack?: RulePack,
 *  searchText?: string,
 *  questions?: OfflineQuestion[],
 *  output: OutputFormat,
 *  sources: {label: string, url: string}[],
 *  suggested_questions?: string[]
 * }} CatalogEntry
 */

export {};
