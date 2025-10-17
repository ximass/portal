export function useMisc() {
  const formatPhone = (raw: string) => {
    let cleaned = raw.replace(/\D/g, '');
    if (cleaned.length > 10) {
      // Formato (99) 99999-9999
      cleaned = cleaned.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
    } else if (cleaned.length > 6) {
      // Formato (99) 9999-9999
      cleaned = cleaned.replace(/^(\d{2})(\d{4})(\d{1,4}).*/, '($1) $2-$3');
    } else if (cleaned.length > 2) {
      // Formato parcial (99) 9999...
      cleaned = cleaned.replace(/^(\d{2})(\d{0,4}).*/, '($1) $2');
    }
    return cleaned;
  };

  const formatCnpj = (raw: string) => {
    let cleaned = raw.replace(/\D/g, '').substring(0, 14);
    // Formato 99.999.999/9999-99
    return cleaned.replace(
      /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/,
      '$1.$2.$3/$4-$5'
    );
  };

  const formatCpf = (raw: string) => {
    let cleaned = raw.replace(/\D/g, '').substring(0, 11);
    // Formato 999.999.999-99
    return cleaned
      .replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4')
      .replace(/^(\d{3})(\d{3})(\d{0,3})$/, '$1.$2.$3')
      .replace(/^(\d{3})(\d{0,3})$/, '$1.$2');
  };

  const formatDateBR = (dateStr: string) => {
    if (!dateStr) return '';

    const date = new Date(dateStr);

    if (isNaN(date.getTime())) return dateStr;

    const pad = (n: number) => n.toString().padStart(2, '0');

    return `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
  };

  const roundValue = (value: number, decimals: number): number => {
    if (value === null || value === undefined) {
      return 0;
    }

    return isNaN(value)
      ? 0
      : Number(parseFloat(value.toString()).toFixed(decimals));
  };

  const getPartImageUrl = (content: string) => {
    const baseUrl = import.meta.env.VITE_API_URL;
    return `${baseUrl}${content}`;
  };

  const ensureNumber = (value: any): number => {
    if (value === null || value === undefined || value === '') {
      return 0;
    }
    const numValue = typeof value === 'string' ? parseFloat(value) : value;
    return isNaN(numValue) ? 0 : numValue;
  };

  const formatDateTimeLocal = (dateStr: string | null): string => {
    if (!dateStr) return '';
    
    // Remove timezone information and microseconds
    // Converts from "2025-10-16T21:47:00.000000Z" to "2025-10-16T21:47:00"
    // or from "2025-10-16 21:47:00" to "2025-10-16T21:47:00"
    const dateWithoutTz = dateStr
      .replace(' ', 'T')
      .replace(/\.\d+Z?$/, '')
      .replace('Z', '');
    
    // Return in the format expected by datetime-local input (yyyy-MM-ddThh:mm:ss)
    return dateWithoutTz.substring(0, 19);
  };

  return {
    formatPhone,
    formatCnpj,
    formatCpf,
    formatDateBR,
    getPartImageUrl,
    roundValue,
    ensureNumber,
    formatDateTimeLocal
  };
}
