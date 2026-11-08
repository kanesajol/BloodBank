import { useEffect, useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { createConsumptionRecord, fetchConsumptionMeta } from '../api/client';

export default function AddConsumptionScreen({ navigation }) {
  const [meta, setMeta] = useState(null);
  const [patientName, setPatientName] = useState('');
  const [age, setAge] = useState('');
  const [sex, setSex] = useState('M');
  const [component, setComponent] = useState('');
  const [units, setUnits] = useState('1');
  const [municipality, setMunicipality] = useState('');
  const [barangay, setBarangay] = useState('');
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    fetchConsumptionMeta()
      .then((data) => {
        setMeta(data);
        setComponent(data.components?.[0] ?? '');
        setMunicipality(data.sdnMunicipalities?.[0] ?? '');
      })
      .catch((e) => setMessage(e.message));
  }, []);

  const barangays = meta?.places?.[municipality] ?? [];

  return (
    <ScrollView style={styles.root} contentContainerStyle={{ padding: 16 }}>
      <Text style={styles.label}>Date submitted</Text>
      <TextInput style={styles.input} value={date} onChangeText={setDate} />
      <Text style={styles.label}>Patient name</Text>
      <TextInput style={styles.input} value={patientName} onChangeText={setPatientName} />
      <Text style={styles.label}>Age</Text>
      <TextInput style={styles.input} value={age} onChangeText={setAge} keyboardType="number-pad" />
      <Text style={styles.label}>Sex</Text>
      <View style={styles.row}>
        {['M', 'F'].map((value) => (
          <Pressable key={value} style={[styles.chip, sex === value && styles.chipActive]} onPress={() => setSex(value)}>
            <Text style={sex === value ? styles.chipTextActive : styles.chipText}>{value}</Text>
          </Pressable>
        ))}
      </View>
      <Text style={styles.label}>Blood component</Text>
      <TextInput style={styles.input} value={component} onChangeText={setComponent} placeholder="Pick from list on web meta" />
      {(meta?.components ?? []).slice(0, 4).map((name) => (
        <Pressable key={name} onPress={() => setComponent(name)}>
          <Text style={styles.option}>{name}</Text>
        </Pressable>
      ))}
      <Text style={styles.label}>Units</Text>
      <TextInput style={styles.input} value={units} onChangeText={setUnits} keyboardType="number-pad" />
      <Text style={styles.label}>Municipality (exact name)</Text>
      <TextInput style={styles.input} value={municipality} onChangeText={setMunicipality} />
      <Text style={styles.label}>Barangay</Text>
      <TextInput style={styles.input} value={barangay} onChangeText={setBarangay} placeholder={barangays[0] ?? ''} />
      <Pressable
        style={styles.button}
        onPress={async () => {
          setSaving(true);
          setMessage('');
          try {
            await createConsumptionRecord({
              date_submitted: date,
              patient_name: patientName.trim(),
              age: parseInt(age, 10),
              sex,
              blood_component: component,
              units: parseInt(units, 10),
              municipality,
              barangay: barangay.trim() || undefined,
            });
            navigation.goBack();
          } catch (e) {
            setMessage(e.message);
          } finally {
            setSaving(false);
          }
        }}
      >
        <Text style={styles.buttonText}>{saving ? 'Saving...' : 'Save record'}</Text>
      </Pressable>
      {message ? <Text style={styles.message}>{message}</Text> : null}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#fff' },
  label: { fontWeight: '600', marginTop: 10, marginBottom: 4 },
  input: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 10, padding: 10 },
  row: { flexDirection: 'row', gap: 8 },
  chip: { borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 8, paddingHorizontal: 14, paddingVertical: 8 },
  chipActive: { backgroundColor: '#990000', borderColor: '#990000' },
  chipText: { color: '#334155' },
  chipTextActive: { color: '#fff', fontWeight: '700' },
  option: { color: '#990000', paddingVertical: 4 },
  button: { backgroundColor: '#990000', borderRadius: 10, padding: 14, alignItems: 'center', marginTop: 20 },
  buttonText: { color: '#fff', fontWeight: '700' },
  message: { marginTop: 12, color: '#dc2626' },
});
