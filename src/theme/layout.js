import { StyleSheet } from 'react-native';

export const colors = {
  primary: '#990000',
  primaryDark: '#7a0000',
  sand: '#FFF8F6',
  text: '#1e293b',
  muted: '#64748b',
  border: '#e2e8f0',
  danger: '#dc2626',
};

export const layout = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: '#fff',
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 16,
  },
  screenTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: colors.primary,
  },
  hint: {
    color: colors.muted,
    fontSize: 13,
    marginTop: 4,
    marginBottom: 12,
    lineHeight: 18,
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 11,
    fontSize: 15,
    minHeight: 44,
  },
  button: {
    backgroundColor: colors.primary,
    borderRadius: 10,
    paddingVertical: 12,
    paddingHorizontal: 16,
    alignItems: 'center',
    minHeight: 44,
    justifyContent: 'center',
  },
  buttonText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 15,
  },
  card: {
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
});
