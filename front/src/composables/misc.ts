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
    return cleaned.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
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

  return {
    formatPhone,
    formatCnpj,
    formatCpf,
    formatDateBR,
  };
}