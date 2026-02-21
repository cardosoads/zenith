export const formatCentsToBrl = (cents) => {
    const value = Number(cents || 0) / 100;

    return value.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
};

export const centsToMaskedBrl = (cents) => {
    const value = Number(cents || 0) / 100;

    return value.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

export const maskedBrlToCents = (maskedValue) => {
    if (!maskedValue) {
        return 0;
    }

    const cleaned = String(maskedValue)
        .replace(/\s/g, '')
        .replace(/R\$/g, '')
        .replace(/\./g, '')
        .replace(',', '.');

    const parsed = Number.parseFloat(cleaned);

    if (Number.isNaN(parsed)) {
        return 0;
    }

    return Math.round(parsed * 100);
};

export const applyMoneyMask = (rawValue) => {
    const digits = String(rawValue ?? '').replace(/\D/g, '');
    const cents = Number.parseInt(digits || '0', 10);

    return centsToMaskedBrl(cents);
};
