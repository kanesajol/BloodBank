import { StyleSheet, Text, View } from 'react-native';

export default function EmptyState({ title = 'Nothing here yet', message = 'Use search or filters to load records.' }) {
  return (
    <View style={styles.wrap}>
      <View style={styles.icon} />
      <Text style={styles.title}>{title}</Text>
      <Text style={styles.message}>{message}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    alignItems: 'center',
    paddingVertical: 28,
    paddingHorizontal: 16,
  },
  icon: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: '#f1f5f9',
    marginBottom: 12,
  },
  title: {
    fontSize: 15,
    fontWeight: '700',
    color: '#475569',
    textAlign: 'center',
  },
  message: {
    marginTop: 6,
    fontSize: 13,
    color: '#94a3b8',
    textAlign: 'center',
    lineHeight: 18,
  },
});
